import { useState } from 'react'
import { useTranslation } from 'react-i18next'
import type { PlayingGame } from '../api/types.ts'
import { useGame } from '../game/useGame.ts'
import { MapView, type LatLng } from '../map/MapView.tsx'
import styles from './RoundScreen.module.css'

export function RoundScreen({ game }: { game: PlayingGame }) {
  const { t } = useTranslation()
  const { submitGuess, nextRound, returnToMenu } = useGame()
  const [guess, setGuess] = useState<LatLng | null>(null)

  return (
    <section className={styles.container}>
      <MapView onGuessChange={setGuess} actualPosition={game.actual} />

      <div className={styles.topLeft}>
        <button onClick={() => returnToMenu()}>{t('round.giveUp')}</button>
      </div>

      <div className={styles.topRight}>
        <p className={styles.roundInfo}>
          {t('round.progress', {
            round: game.round,
            totalRounds: game.totalRounds,
            score: game.runningScore,
          })}
        </p>
      </div>

      <div className={styles.bottomCenter}>
        <p>
          {t('round.cityPopulation', {
            name: game.city.name,
            population: game.city.population,
          })}
        </p>

        {game.distanceKm === undefined ? (
          <button
            disabled={guess === null}
            onClick={() => {
              if (guess) {
                submitGuess(guess.latitude, guess.longitude)
              }
            }}
          >
            {t('round.confirmGuess')}
          </button>
        ) : (
          <>
            <p>
              {t('round.result', {
                distanceKm: game.distanceKm.toFixed(2),
                score: game.score,
              })}
            </p>
            <button onClick={() => nextRound()}>{t('round.next')}</button>
          </>
        )}
      </div>
    </section>
  )
}
