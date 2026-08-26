// Thresholds are per-round score (0-1000), sorted highest first.
const SCORE_RATINGS: { threshold: number; emoji: string }[] = [
  { threshold: 900, emoji: '🎯' },
  { threshold: 750, emoji: '⭐' },
  { threshold: 500, emoji: '👍' },
  { threshold: 250, emoji: '😐' },
  { threshold: 100, emoji: '😬' },
  { threshold: 0, emoji: '💀' },
]

export function getScoreEmoji(score: number): string {
  return (SCORE_RATINGS.find((rating) => score >= rating.threshold) ?? SCORE_RATINGS[SCORE_RATINGS.length - 1]).emoji
}
