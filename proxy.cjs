/**
 * Lightweight reverse proxy (Node.js built-ins only).
 *
 * Port 5000 (public Replit port):
 *   - WebSocket connections whose path starts with /app/  →  Reverb  (8080)
 *   - Everything else (HTTP)                              →  Laravel (4000)
 *
 * This lets the browser reach Reverb via wss://REPLIT_DOMAIN/app/...
 * without needing a separate port or Nginx.
 */
const http = require('http');
const net  = require('net');

const LARAVEL = { host: '127.0.0.1', port: 4000 };
const REVERB  = { host: '127.0.0.1', port: 8080 };

/* ── HTTP proxy ─────────────────────────────────────────── */
const server = http.createServer((req, res) => {
    const { host, port } = LARAVEL;
    const opts = {
        host, port,
        path:    req.url,
        method:  req.method,
        headers: { ...req.headers, 'x-forwarded-proto': 'https' },
    };
    const proxy = http.request(opts, (upstream) => {
        res.writeHead(upstream.statusCode, upstream.headers);
        upstream.pipe(res, { end: true });
    });
    proxy.on('error', () => { try { res.writeHead(502); res.end('Gateway error'); } catch(_){} });
    req.pipe(proxy, { end: true });
});

/* ── WebSocket proxy ────────────────────────────────────── */
server.on('upgrade', (req, clientSocket, head) => {
    // Route /app/* to Reverb, everything else to Laravel
    const target = req.url.startsWith('/app/') ? REVERB : LARAVEL;

    const serverSocket = net.connect(target.port, target.host, () => {
        // Re-send the original HTTP upgrade request to the target
        let handshake = `${req.method} ${req.url} HTTP/1.1\r\n`;
        for (const [k, v] of Object.entries(req.headers)) {
            handshake += `${k}: ${v}\r\n`;
        }
        handshake += '\r\n';
        serverSocket.write(handshake);
        if (head && head.length) serverSocket.write(head);
    });

    serverSocket.pipe(clientSocket);
    clientSocket.pipe(serverSocket);

    const destroy = () => { try { clientSocket.destroy(); } catch(_){} try { serverSocket.destroy(); } catch(_){} };
    serverSocket.on('error', destroy);
    clientSocket.on('error', destroy);
});

server.listen(5000, '0.0.0.0', () => console.log('[proxy] listening on :5000'));
