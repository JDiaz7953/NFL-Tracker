import { useState, useEffect } from "react";
import PickCard from "./components/PickCard";
import "./App.css";

const statOptions = {
  receiving: [
    { value: "receptions", label: "Receptions" },
    { value: "receiving_yards", label: "Receiving Yards" },
    { value: "receiving_tds", label: "Receiving TDs" },
  ],
  rushing: [
    { value: "rush_attempts", label: "Rush Attempts" },
    { value: "rushing_yards", label: "Rushing Yards" },
    { value: "rushing_tds", label: "Rushing TDs" },
  ],
  passing: [
    { value: "pass_attempts", label: "Pass Attempts" },
    { value: "passing_yards", label: "Passing Yards" },
    { value: "passing_tds", label: "Passing TDs" },
  ],
};

function App() {
  const [players, setPlayers] = useState([]);
  const [playerName, setPlayerName] = useState("");
  const [category, setCategory] = useState("");
  const [stat, setStat] = useState("");
  const [value, setValue] = useState("");
  const [overUnder, setOverUnder] = useState("Over");
  const [picks, setPicks] = useState([]);

  useEffect(() => {
    fetch("http://localhost:5050/api/players")
      .then(res => res.json())
      .then(data => setPlayers(data))
      .catch(console.error);
  }, []);

  const handleAddPick = () => {
    console.log('pressed');
    console.log('playerName:', playerName);
    console.log('category:', category);
    console.log('stat:', stat);
    console.log('value:', value);
  
    if (!playerName || !category || !stat || !value) {
      console.warn('⚠️ Missing field, cannot submit pick');
      return;
    }
  
    const p = players.find(p => p.name.toLowerCase().trim() === playerName.toLowerCase().trim());
    if (!p) {
      console.warn('⚠️ Player not found in player list');
      return;
    }
  
    console.log('✅ Adding pick:', p.name);
  
    setPicks([
      ...picks,
      {
        playerId: p.id,
        playerName: p.name,
        team: p.team,
        category,
        stat,
        value,
        overUnder,
      },
    ]);
  
    setPlayerName("");
    setCategory("");
    setStat("");
    setValue("");
    setOverUnder("Over");
  };
  

  return (
    <main className="flex flex-col items-center mt-8 space-y-6">
      {/* Form */}
      <div className="flex items-center bg-zinc-800 rounded-2xl shadow-xl p-4 space-x-2">
        {/* Search player */}
        <input
          type="text"
          placeholder="Search For A Player"
          list="player-list"
          value={playerName}
          onChange={e => setPlayerName(e.target.value)}
          className="text-white p-3 w-48 border rounded-lg"
        />
        <datalist id="player-list">
          {players.map(pl => (
            <option key={pl.id} value={pl.name} />
          ))}
        </datalist>

        {/* Target value */}
        <input
          type="number"
          placeholder="value"
          value={value}
          onChange={e => setValue(e.target.value)}
          className="text-white p-3 w-20 border rounded-lg"
        />

        {/* Category */}
        <select
          value={category}
          onChange={e => {
            setCategory(e.target.value);
            setStat("");
          }}
          className="text-white p-3 w-28 border rounded-lg"
        >
          <option value="">Category</option>
          <option value="receiving">Receiving</option>
          <option value="rushing">Rushing</option>
          <option value="passing">Passing</option>
        </select>

        {/* Stat */}
        <select
          value={stat}
          onChange={e => setStat(e.target.value)}
          className="text-white p-3 w-40 border rounded-lg disabled:opacity-50"
          disabled={!category}
        >
          <option value="">Stat</option>
          {category && statOptions[category].map(s => (
            <option key={s.value} value={s.value}>
              {s.label}
            </option>
          ))}
        </select>

        {/* Over/Under */}
        <select
          value={overUnder}
          onChange={e => setOverUnder(e.target.value)}
          className="text-white p-3 w-24 border rounded-lg"
        >
          <option value="Over">Over</option>
          <option value="Under">Under</option>
        </select>

        {/* Submit */}
        <button
          onClick={handleAddPick}
          className="bg-white rounded-xl w-9 h-9 flex items-center justify-center"
        >
          ➕
        </button>
      </div>

      {/* Picks */}
      {picks.length > 0 && (
        <div className="flex flex-wrap justify-center gap-4">
          {picks.map((pick, i) => (
            <PickCard key={i} pick={pick} />
          ))}
        </div>
      )}
    </main>
  );
}

export default App;
