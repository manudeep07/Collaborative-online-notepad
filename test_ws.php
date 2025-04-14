<!DOCTYPE html>
<html>
<head><title>Test WebSocket</title></head>
<body>
    <script>
        const ws = new WebSocket('ws://localhost:8080');
        ws.onopen = () => {
            console.log('Connected');
            ws.send('Hello Ratchet!');
        };
        ws.onmessage = (event) => {
            console.log('Received:', event.data);
        };
        ws.onclose = () => {
            console.log('Disconnected');
        };
        ws.onerror = (error) => {
            console.error('Error:', error);
        };
    </script>
</body>
</html>