import { useTranslation } from 'react-i18next'
import type { Mode } from '../api/types.ts'
import { useGame } from '../game/useGame.ts'
import styles from './MenuScreen.module.css'

const MODES: Mode[] = ['easy', 'medium', 'hard']

export function MenuScreen() {
  const { t } = useTranslation()
  const { startGame } = useGame()

  return (
    <section className={styles.container}>
      <h1>{t('menu.title')}</h1>
      <div className={styles.modes}>
        {MODES.map((mode) => (
          <button key={mode} onClick={() => startGame(mode)}>
            {t(`menu.modes.${mode}`)}
          </button>
        ))}
      </div>
    </section>
  )
}
