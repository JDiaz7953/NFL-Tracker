// test-server.js
const express = require('express');
const app = express();
const port = 5050;

console.log("🚀 Test server starting...");

app.get("/test", (req, res) => {
  console.log("✅ /test route hit");
  res.send("Hello from simple test server!");
});

app.listen(port, () => {
  console.log(`✅ Test server listening at http://localhost:${port}`);
});
