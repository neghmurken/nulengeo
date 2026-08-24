import type { Mode } from '../api/types.ts'
import { useGame } from '../game/useGame.ts'

const MODES: Mode[] = ['easy', 'medium', 'hard']

export function MenuScreen() {
  const { startGame } = useGame()

  return (
    <section>
      <h1>Nulengeo</h1>
      {MODES.map((mode) => (
        <button key={mode} onClick={() => startGame(mode)}>
          {mode}
        </button>
      ))}
    </section>
  )
}
