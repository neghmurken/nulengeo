import { useTranslation } from 'react-i18next'
import type { FinishedGame } from '../api/types.ts'
import { useGame } from '../game/useGame.ts'
import styles from './ResultsScreen.module.css'

export function ResultsScreen({ game }: { game: FinishedGame }) {
  const { t } = useTranslation()
  const { returnToMenu } = useGame()

  return (
    <section className={styles.container}>
      <h1>{t('results.totalScore', { score: game.totalScore })}</h1>
      <ul className={styles.results}>
        {game.results.map((result) => (
          <li key={result.inseeCode} className={styles.result}>
            <span className={styles.name}>{result.name}</span>
            <span className={styles.distance}>
              {t('results.distance', {
                distanceKm: result.distanceKm.toFixed(2),
              })}
            </span>
            <span className={styles.score}>
              {t('results.score', { score: result.score })}
            </span>
          </li>
        ))}
      </ul>
      <button onClick={() => returnToMenu()}>{t('results.backToMenu')}</button>
    </section>
  )
}
