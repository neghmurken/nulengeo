import type { PlayingGame } from '../api/types.ts'
import { useGame } from '../game/useGame.ts'

export function RoundScreen({ game }: { game: PlayingGame }) {
  const { submitGuess, nextRound } = useGame()

  return (
    <section>
      <p>
        Round {game.round} / {game.totalRounds} — score: {game.runningScore}
      </p>
      <p>
        {game.city.name} ({game.city.population} inhabitants)
      </p>

      {/* map placeholder — real click-to-place input lands in a later step */}
      {game.distanceKm === undefined ? (
        <button onClick={() => submitGuess(0, 0)}>
          Submit guess (stub: 0, 0)
        </button>
      ) : (
        <>
          <p>
            Distance: {game.distanceKm} km — score: {game.score}
          </p>
          <button onClick={() => nextRound()}>Next</button>
        </>
      )}
    </section>
  )
}
