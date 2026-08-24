import { useTranslation } from 'react-i18next'
import { useGame } from './game/useGame.ts'
import { MenuScreen } from './screens/MenuScreen.tsx'
import { RoundScreen } from './screens/RoundScreen.tsx'
import { ResultsScreen } from './screens/ResultsScreen.tsx'

function App() {
  const { t } = useTranslation()
  const { state } = useGame()

  if (state.status === 'loading') {
    return <p>{t('app.loading')}</p>
  }

  if (state.status === 'error') {
    return <p>{t('app.error', { title: state.error.title })}</p>
  }

  switch (state.game.status) {
    case 'idle':
      return <MenuScreen />
    case 'playing':
      return <RoundScreen key={state.game.round} game={state.game} />
    case 'finished':
      return <ResultsScreen game={state.game} />
  }
}

export default App
