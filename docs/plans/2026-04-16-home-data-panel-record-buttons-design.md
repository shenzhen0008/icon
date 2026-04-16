# Home Data Panel Record Buttons Design

**Goal:** Hide the `交易记录` and `收益记录` buttons from the homepage `home-data-panel` without removing the underlying record pages or changing shared panel usage elsewhere.

**Context**

- The homepage hero panel renders two record buttons inside `resources/views/components/home/hero.blade.php`.
- The user wants the homepage buttons hidden, but the panel structure is reused and the record pages must remain available.
- The smallest safe change is to stop rendering the two buttons in the homepage hero component while leaving the route and supporting script intact.

**Recommended Approach**

1. Add a focused homepage test that asserts the two button IDs and labels are absent.
2. Remove the rendered button block from the homepage hero component only.
3. Keep the record page routes and tests unchanged.
4. Leave the existing mode sync helper harmless if the buttons are absent.

**Why This Approach**

- Touches only the homepage rendering surface.
- Avoids changing other panel consumers or route behavior.
- Matches the user's request to hide the buttons instead of deleting feature support.
