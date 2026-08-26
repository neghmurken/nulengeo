import { describe, expect, it, vi } from 'vitest'
import { render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { MenuScreen } from './MenuScreen.tsx'
import { useGame } from '../game/useGame.ts'
import { getModes } from '../api/client.ts'

vi.mock('../game/useGame.ts')
vi.mock('../api/client.ts')

describe('MenuScreen', () => {
  it('starts a game in the clicked mode', async () => {
    const startGame = vi.fn()
    vi.mocked(useGame).mockReturnValue({
      state: { status: 'ready', game: { status: 'idle' } },
      startGame,
      submitGuess: vi.fn(),
      nextRound: vi.fn(),
      returnToMenu: vi.fn(),
    })
    vi.mocked(getModes).mockResolvedValue({
      modes: ['easy', 'medium', 'hard', 'expert'],
    })

    render(<MenuScreen />)

    expect(
      await screen.findByRole('button', { name: /^Facile/ }),
    ).toBeInTheDocument()
    expect(screen.getByRole('button', { name: /^Moyen/ })).toBeInTheDocument()
    expect(
      screen.getByRole('button', { name: /^Difficile/ }),
    ).toBeInTheDocument()
    expect(screen.getByRole('button', { name: /^Expert/ })).toBeInTheDocument()

    await userEvent.click(screen.getByRole('button', { name: /^Difficile/ }))

    expect(startGame).toHaveBeenCalledWith('hard')
  })
})
