//import { useState } from 'react'
import "./App.css";
import NavBar from "./components/NavBar";
import Header from "./components/header";

function App() {
  return (
    <>
      <Header />
      <main className="flex justify-center items-center">
        <div className="flex w-200 h-200 justify-center items-center">
          <div className="flex  items-center justify-start bg-zinc-800 h-30 w-175 rounded-2xl shadow-xl ">
            <div className="flex flex-1 ">
              <input
                type="text"
                placeholder="Search For A Player"
                className="text-white
             outline-0 p-3 w-50 border rounded-lg ml-2 relative"
              />
              <input
                type="text"
                placeholder="value"
                className="text-white
             outline-0 p-3 w-25 border rounded-lg ml-2 relative"
              />
              <select
                name="stats"
                id="id"
                className="text-white
             outline-0 p-3 w-25 border rounded-lg ml-2 relative"
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
                className="
             outline-0 p-3 w-25 border border-white text-white rounded-lg ml-2 relative"
              >
                <option className="text-white" value="Over">
                  Over
                </option>
                <option value="Under">Under</option>
              </select>
              <select
                name="stats"
                id="id"
                className="text-white
             outline-0 p-3 w-25 border rounded-lg ml-2 relative"
              >
                <option value=""></option>
                <option value=""></option>
                <option value=""></option>
                <option value=""></option>
                <option value=""></option>
              </select>
              <button className="bg-white mt-2 ml-2 flex rounded-xl w-9 h-9 justify-center items-center ">
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
      </main>
    </>
  );
}

export default App;
