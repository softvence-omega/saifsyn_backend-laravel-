const express = require("express");
const http = require("http");
const { Server } = require("socket.io");
const cors = require("cors");

const app = express();
app.use(cors());
app.use(express.json());

const server = http.createServer(app);

const io = new Server(server, {
  cors: { origin: "*", methods: ["GET","POST"] }
});

io.on("connection", (socket) => {
  console.log("User connected:", socket.id);

  // join private room
  socket.on("joinRoom", (userId) => {
    socket.join("user_" + userId);
    console.log("User joined room: user_" + userId);
  });

  // send message
  socket.on("sendMessage", (data) => {
    io.to("user_" + data.receiver_id).emit("receiveMessage", data);
  });

  socket.on("disconnect", () => {
    console.log("User disconnected:", socket.id);
  });
});

server.listen(3000, () => {
  console.log("Socket.IO server running at http://localhost:3000");
});
