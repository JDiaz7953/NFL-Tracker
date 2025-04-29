//added the player part to test the server connectction 
// you can remove it 

import { useEffect, useState } from 'react';
import "./App.css";
import NavBar from "./components/NavBar";
import Header from "./components/Header";

function App() {
  const [players, setPlayers] = useState([]);

  useEffect(() => {
    fetch('http://localhost:5000/api/players')
      .then((res) => res.json())
      .then((data) => setPlayers(data))
      .catch((err) => console.error("Error fetching players:", err));
  }, []);

  return (
    <>
      <Header />
      <main className="flex flex-col items-center">
        <div className="flex w-200 h-200 justify-center items-center">
          <div className="flex items-center justify-start bg-zinc-800 h-30 w-175 rounded-2xl shadow-xl">
            <div className="flex flex-1">
              <input
                type="text"
                placeholder="Search For A Player"
                className="text-white outline-0 p-3 w-50 border rounded-lg ml-2 relative"
              />
              <input
                type="text"
                placeholder="value"
                className="text-white outline-0 p-3 w-25 border rounded-lg ml-2 relative"
              />
              <select
                name="stats"
                id="id"
                className="text-white outline-0 p-3 w-25 border rounded-lg ml-2 relative"
              >
                <option value=""></option>
                <option value=""></option>
                <option value=""></option>
                <option value=""></option>
                <option value=""></option>
              </select>
              <select
                name="stats"
                id="id"
                className="outline-0 p-3 w-25 border border-white text-white rounded-lg ml-2 relative"
              >
                <option className="text-white" value="Over">
                  Over
                </option>
                <option value="Under">Under</option>
              </select>
              <select
                name="stats"
                id="id"
                className="text-white outline-0 p-3 w-25 border rounded-lg ml-2 relative"
              >
                <option value=""></option>
                <option value=""></option>
                <option value=""></option>
                <option value=""></option>
                <option value=""></option>
              </select>
              <button className="bg-white mt-2 ml-2 flex rounded-xl w-9 h-9 justify-center items-center">
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                  strokeWidth={1.5}
                  stroke="currentColor"
                  className="size-6"
                >
                  <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    d="M8.25 6.75 12 3m0 0 3.75 3.75M12 3v18"
                  />
                </svg>
              </button>
            </div>
          </div>
        </div>

        {/* Player Table */}
        <div className="mt-6 w-full max-w-4xl">
          <h2 className="text-white text-xl mb-4">Player List</h2>
          <table className="min-w-full bg-zinc-800 text-white rounded-lg overflow-hidden">
            <thead>
              <tr>
                <th className="py-2 px-4 border-b border-zinc-700"> Player ID</th>
                <th className="py-2 px-4 border-b border-zinc-700">Name</th>
                <th className="py-2 px-4 border-b border-zinc-700">Team ID </th>
                
                {/* Add more headers as needed */}
              </tr>
            </thead>
            <tbody>
              {players.map((player) => (
                <tr key={player.id} className="hover:bg-zinc-700">
                  <td className="py-2 px-4 border-b border-zinc-700">{player.player_id}</td>
                  <td className="py-2 px-4 border-b border-zinc-700">{player.full_name}</td>
                  <td className="py-2 px-4 border-b border-zinc-700">{player.team_id}</td>

                  {/* Add more cells as needed */}
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </main>
    </>
  );
}

export default App;