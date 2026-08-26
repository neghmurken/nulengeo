import { describe, expect, it } from 'vitest'
import { getScoreEmoji } from './scoreRating.ts'

describe('getScoreEmoji', () => {
  it.each([
    [1000, '🎯'],
    [900, '🎯'],
    [899, '⭐'],
    [750, '⭐'],
    [500, '👍'],
    [250, '😐'],
    [100, '😬'],
    [99, '💀'],
    [0, '💀'],
  ])('rates a score of %i as %s', (score, expected) => {
    expect(getScoreEmoji(score)).toBe(expected)
  })
})
