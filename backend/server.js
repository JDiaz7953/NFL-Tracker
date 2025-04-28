//start the server withh node server.js

const express = require('express');
const mysql = require('mysql2');
const cors = require('cors');
require('dotenv').config();

const app = express();
const port = process.env.PORT || 5000;

app.use(cors());
app.use(express.json());

const db = mysql.createConnection({
  host: process.env.DB_HOST,
  user: process.env.DB_USER,
  password: process.env.DB_PASSWORD,
  database: process.env.DB_NAME
});

db.connect(err => {
  if (err) {
    console.error('Error connecting to DB:', err);
    return;
  }
  console.log('Connected to AWS MySQL database');
});


app.get('/', (req, res) => {
  res.send('backend running');
});

app.get('/api/players', (req, res) => {
  db.query('SELECT * FROM players LIMIT 10', (err, results) => {
    if (err) {
      console.error('Error fetching players:', err);
      res.status(500).json({ error: 'Failed to fetch players' });
    } else {
      res.json(results);
    }
  });
});


app.use((req, res) => {
  res.status(404).send('Route not found');
});

app.listen(port, () => {
  console.log(`Backend server running at http://localhost:${port}`);
});
