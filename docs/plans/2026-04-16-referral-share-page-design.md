# Referral Share Page Design

## Context

The project already has a working `/referral` dashboard that exposes the invite code, invite link, referral rates, and first- and second-level referral user collections. The user now wants that page redesigned to match a supplied mobile reference image more closely, with a compact single-page share-focused layout instead of a data list dashboard.

This is a user-facing Blade/Tailwind page inside the existing Laravel 13 frontend shell. The redesign should stay within the current visual system, reuse the available referral data, and follow the same theme-variable system used by the site's other public pages instead of using fixed one-off colors.

## Chosen Approach

Adopt a high-fidelity mobile-first share page for `/referral`:

- a blue visual hero at the top to echo the reference
- a single dominant white card overlapping the hero
- fixed commission highlights for level one and level two
- total invite counts only, without rendering referral user lists
- invite code and invite link stacked below the metrics
- prominent copy/share actions at the bottom of the card

This keeps the page aligned with the reference while preserving the existing route, controller, and referral data source, and it lets the page respond correctly to the site's theme switching.

## Information Architecture

From top to bottom, the page should show:

1. Hero area with a share/invite message and supporting illustration treatment
2. Main card with:
   - level one commission `5%`
   - level two commission `2%`
   - level one invite count
   - level two invite count
   - invite code
   - invite link
   - copy and share actions

The page should prioritize fitting the full experience within a single mobile page or as close to it as practical. Existing detailed first-level and second-level referral user lists should be removed from the page.

## Data Rules

- Level one commission displays as a fixed `5%`
- Level two commission displays as a fixed `2%`
- Level one invite count is derived from the existing `level_one_users` collection count
- Level two invite count is derived from the existing `level_two_users` collection count
- Invite code and invite URL continue to come from the existing referral dashboard service

## UI Notes

- Keep the page mobile-first and visually close to the provided reference
- Keep the page inside the standard frontend shell with `bg-theme`, `text-theme`, and `x-layout.background-glow`
- Use theme variables for surfaces, text, borders, and buttons
- Keep a strong visual hero, but derive it from `theme-primary` and `theme-accent` instead of hard-coded colors
- Keep border radius within the project constraints
- Keep the page vertically compact so the main content appears in one view on common phones
- Preserve the current navigation shell and mobile bottom navigation
- Allow the referral card, nested metric cards, and reward-help bubble to adapt in both business and tech themes

## Testing

Update the referral feature test to assert the new structure and copy:

- `/referral` still redirects guests to `/me`
- authenticated users still get an invite code generated when missing
- the redesigned page shows the key sections and actions
- the page uses the same theme-variable shell patterns as other public pages
- the page shows the invite URL and the fixed `5%` and `2%` commission values
- the page shows total first-level and second-level invite counts instead of referral user list sections
