import { useTranslation } from 'react-i18next'
import type { FinishedGame } from '../api/types.ts'
import { useGame } from '../game/useGame.ts'

export function ResultsScreen({ game }: { game: FinishedGame }) {
  const { t } = useTranslation()
  const { returnToMenu } = useGame()

  return (
    <section>
      <h1>{t('results.totalScore', { score: game.totalScore })}</h1>
      <ul>
        {game.results.map((result) => (
          <li key={result.inseeCode}>
            {t('results.result', {
              name: result.name,
              distanceKm: result.distanceKm,
              score: result.score,
            })}
          </li>
        ))}
      </ul>
      <button onClick={() => returnToMenu()}>{t('results.backToMenu')}</button>
    </section>
  )
}
