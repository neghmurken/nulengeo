import { useTranslation } from 'react-i18next'
import type { FinishedGame } from '../api/types.ts'
import { getScoreEmoji } from '../game/scoreRating.ts'
import { useGame } from '../game/useGame.ts'
import styles from './ResultsScreen.module.css'

export function ResultsScreen({ game }: { game: FinishedGame }) {
  const { t } = useTranslation()
  const { returnToMenu } = useGame()

  return (
    <section className={styles.container}>
      <h1>
        {t('results.totalScore', { score: game.totalScore })}
        <span className={styles.maxScore}>
          {t('results.maxScore', { maxScore: game.maxScore })}
        </span>
      </h1>
      <ul className={styles.results}>
        {game.results.map((result, index) => {
          const [distanceInteger, distanceDecimals] = result.distanceKm
            .toFixed(2)
            .split('.')

          return (
            <li key={result.inseeCode} className={styles.result}>
              <span className={styles.round}>
                {t('results.round', { round: index + 1 })}
              </span>
              <span className={styles.name}>{result.name}</span>
              <span className={styles.distance}>
                {distanceInteger}
                <span className={styles.muted}>
                  .{distanceDecimals} {t('results.distanceUnit')}
                </span>
              </span>
              <span className={styles.score}>
                {result.score}
                <span className={styles.muted}> {t('results.scoreUnit')}</span>
              </span>
              <span className={styles.rating}>
                {getScoreEmoji(result.score)}
              </span>
            </li>
          )
        })}
      </ul>
      <button onClick={() => returnToMenu()}>{t('results.backToMenu')}</button>
    </section>
  )
}
