import { useTranslation } from 'react-i18next'
import type { PlayingGame } from '../api/types.ts'
import { useGame } from '../game/useGame.ts'

export function RoundScreen({ game }: { game: PlayingGame }) {
  const { t } = useTranslation()
  const { submitGuess, nextRound } = useGame()

  return (
    <section>
      <p>
        {t('round.progress', {
          round: game.round,
          totalRounds: game.totalRounds,
          score: game.runningScore,
        })}
      </p>
      <p>
        {t('round.cityPopulation', {
          name: game.city.name,
          population: game.city.population,
        })}
      </p>

      {/* map placeholder — real click-to-place input lands in a later step */}
      {game.distanceKm === undefined ? (
        <button onClick={() => submitGuess(0, 0)}>
          {t('round.submitStub')}
        </button>
      ) : (
        <>
          <p>
            {t('round.result', {
              distanceKm: game.distanceKm,
              score: game.score,
            })}
          </p>
          <button onClick={() => nextRound()}>{t('round.next')}</button>
        </>
      )}
    </section>
  )
}
