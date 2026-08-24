import type { FinishedGame } from '../api/types.ts'
import { useGame } from '../game/useGame.ts'

export function ResultsScreen({ game }: { game: FinishedGame }) {
  const { returnToMenu } = useGame()

  return (
    <section>
      <h1>Total score: {game.totalScore}</h1>
      <ul>
        {game.results.map((result) => (
          <li key={result.inseeCode}>
            {result.name} — {result.distanceKm} km — {result.score} pts
          </li>
        ))}
      </ul>
      <button onClick={() => returnToMenu()}>Back to menu</button>
    </section>
  )
}
