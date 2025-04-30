import { useEffect, useState } from "react";

export default function PickCard({ pick }) {
  const [prob, setProb] = useState(null);

  useEffect(() => {
    const params = new URLSearchParams({
      playerId: pick.playerId,
      category: pick.category,
      stat: pick.stat,
      value: pick.value,
    }).toString();

    fetch(`http://localhost:5000/api/likelihood?${params}`)
      .then(r => r.json())
      .then(data => setProb(data.probability))
      .catch(() => setProb(0));
  }, [pick]);

  return (
    <div className="border border-white rounded-xl p-4 flex flex-col space-y-2 w-64">
      <span className="font-bold text-lg">{pick.playerName}</span>
      <span>
        {pick.stat.replace("_", " ")} {pick.overUnder} {pick.value}
      </span>
      <span>vs. {pick.team}</span>
      <span className="mt-2 text-sm">
        {prob === null ? "Calculating…" : `Likelihood: ${prob}%`}
      </span>
    </div>
  );
}
