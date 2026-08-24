import { useGame } from './game/useGame.ts'
import { MenuScreen } from './screens/MenuScreen.tsx'
import { RoundScreen } from './screens/RoundScreen.tsx'
import { ResultsScreen } from './screens/ResultsScreen.tsx'

function App() {
  const { state } = useGame()

  if (state.status === 'loading') {
    return <p>Loading...</p>
  }

  if (state.status === 'error') {
    return <p>Error: {state.error.title}</p>
  }

  switch (state.game.status) {
    case 'idle':
      return <MenuScreen />
    case 'playing':
      return <RoundScreen game={state.game} />
    case 'finished':
      return <ResultsScreen game={state.game} />
  }
}

export default App
