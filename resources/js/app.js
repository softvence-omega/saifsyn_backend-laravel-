import './bootstrap';
import { io } from "socket.io-client";

// Make socket globally accessible
window.socket = io(import.meta.env.VITE_SOCKET_URL);

window.socket.on("connect", () => {
    console.log("✅ Connected to Socket.IO server:", window.socket.id);
});

// Message receive
window.socket.on("receiveMessage", (data) => {
    console.log("📩 Message received:", data);
});
