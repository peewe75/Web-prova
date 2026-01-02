const express = require('express');
const http = require('http');
const { Server } = require("socket.io");
const cors = require('cors');

const app = express();
app.use(cors());

const server = http.createServer(app);
const io = new Server(server, {
  cors: {
    origin: "*", // Adjust for production
    methods: ["GET", "POST"]
  }
});

io.on('connection', (socket) => {
  console.log('A user connected:', socket.id);

  // --- EVENTS CLIENT -> ADMIN ---

  // 1. Appointment Requested
  socket.on('appointment_requested', (data) => {
    console.log('New Appointment Request:', data);
    // Notify Admin Dashboard
    io.emit('admin_appointment_notification', data);
  });

  // 2. Document Uploaded
  socket.on('document_uploaded', (data) => {
    console.log('New Document:', data);
    io.emit('admin_document_notification', data);
  });

  // 3. Message Sent (Client -> Admin)
  socket.on('message_sent', (data) => {
      console.log('Message from Client:', data);
      io.emit('admin_message_notification', data);
  });
  
  // 4. Note Added (If client adds notes? Or system?)
  // Usually notes are admin side, but if legal notes are added, admin should know
  socket.on('note_added', (data) => {
      io.emit('admin_note_notification', data);
  });


  // --- EVENTS ADMIN -> CLIENT ---

  // 5. Appointment Status Update (Accepted/Rejected)
  socket.on('appointment_status_update', (data) => {
      console.log('Appointment Update:', data);
      // Notify Specific Client (or all for simplicity now, client filters by ID)
      io.emit('client_appointment_update', data);
  });

  // 6. Message Sent (Admin -> Client)
  socket.on('admin_message_sent', (data) => {
      console.log('Message from Admin:', data);
      io.emit('client_message_notification', data);
  });

  // 7. General Notification
  socket.on('send_notification', (data) => {
      io.emit('client_general_notification', data);
  });


  socket.on('disconnect', () => {
    console.log('User disconnected:', socket.id);
  });
});

const PORT = 3000;
server.listen(PORT, () => {
  console.log(`Socket.io Server running on port ${PORT}`);
});
