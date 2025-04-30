// backend/server.js

require("dotenv").config();
const express = require("express");
const mysql = require("mysql2");

const app = express();
const port = process.env.PORT || 5050;

// Allow React frontend at port 5173 to access the backend
app.use((req, res, next) => {
  res.header("Access-Control-Allow-Origin", "*");
  res.header("Access-Control-Allow-Headers", "Content-Type, Authorization");
  res.header("Access-Control-Allow-Methods", "GET, POST, OPTIONS");
  if (req.method === "OPTIONS") {
    return res.sendStatus(200);
  }
  next();
});

app.use(express.json());

// ✅ Create MySQL connection
const db = mysql.createConnection({
  host: process.env.DB_HOST,
  user: process.env.DB_USER,
  password: process.env.DB_PASSWORD,
  database: process.env.DB_NAME,
  port: process.env.DB_PORT || 3306,
});

// ✅ Confirm connection
db.connect(err => {
  if (err) {
    console.error("Error connecting to DB:", err);
    return;
  }
  console.log("Connected to AWS MySQL database");
});

// Test route
app.get("/test", (req, res) => {
  console.log("/test route hit");
  res.send("Server is running on port 5050");
});

// ✅ Route: Get a player by name
app.get("/api/players", (req, res) => {
  const name = req.query.name || req.query.full_name;
  let sql = "SELECT player_id AS id, full_name AS name, team_id AS team FROM players";
  const params = [];

  if (name) {
    sql += " WHERE full_name=?";
    params.push(`%${name}%`);
    
  } else {
    sql += " LIMIT 20";
  }

  db.query(sql, params, (err, rows) => {
    if (err) {
      console.error("DB error:", err);
      return res.status(500).json({ error: err.message });
    }
    res.json(rows);
  });
});

// ✅ Route: Calculate likelihood
app.get("/api/likelihood", (req, res) => {
  const { playerId, category, stat, value } = req.query;

  if (!playerId || !category || !stat || !value) {
    return res.status(400).json({ error: "Missing query params" });
  }

  const tableMap = {
    receiving: "receiving_stats",
    rushing: "rushing_stats",
    passing: "passing_stats",
  };

  const allowed = {
    receiving: ["receptions", "receiving_yards", "receiving_tds"],
    rushing: ["rush_attempts", "rushing_yards", "rushing_tds"],
    passing: ["pass_attempts", "passing_yards", "passing_tds"],
  };

  const table = tableMap[category];
  if (!table || !allowed[category].includes(stat)) {
    return res.status(400).json({ error: "Invalid category or stat" });
  }

  const totalSql = `SELECT COUNT(*) AS total FROM ${table} WHERE player_id = ?`;
  const successSql = `SELECT COUNT(*) AS success FROM ${table} WHERE player_id = ? AND ${stat} >= ?`;

  db.query(totalSql, [playerId], (err1, totalRows) => {
    if (err1) return res.status(500).json({ error: err1.message });
    const total = totalRows[0].total;

    db.query(successSql, [playerId, +value], (err2, successRows) => {
      if (err2) return res.status(500).json({ error: err2.message });
      const success = successRows[0].success;
      const probability = total > 0 ? Math.round((success / total) * 100) : 0;
      res.json({ probability });
    });
  });
});

// Fallback for undefined routes
app.use((req, res) => {
  console.log("404 Route hit:", req.method, req.url);
  res.status(404).json({ error: "Route not found" });
});

// Start the server
app.listen(port, () => {
  console.log(`Backend server running at http://localhost:${port}`);
});
