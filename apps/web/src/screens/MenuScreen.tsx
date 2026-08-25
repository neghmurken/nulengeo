import { useEffect, useState } from 'react'
import { useTranslation } from 'react-i18next'
import type { Mode, ProblemError } from '../api/types.ts'
import { getModes } from '../api/client.ts'
import { useGame } from '../game/useGame.ts'
import styles from './MenuScreen.module.css'

export function MenuScreen() {
  const { t } = useTranslation()
  const { startGame } = useGame()
  const [modes, setModes] = useState<Mode[]>()
  const [error, setError] = useState<ProblemError>()

  useEffect(() => {
    getModes()
      .then(({ modes }) => setModes(modes))
      .catch(setError)
  }, [])

  if (error) {
    return <p>{t('app.error', { title: error.title })}</p>
  }

  if (!modes) {
    return <p>{t('app.loading')}</p>
  }

  return (
    <section className={styles.container}>
      <h1>{t('menu.title')}</h1>
      <div className={styles.modes}>
        {modes.map((mode) => (
          <button key={mode} onClick={() => startGame(mode)}>
            {t(`menu.modes.${mode}`)}
          </button>
        ))}
      </div>
    </section>
  )
}
