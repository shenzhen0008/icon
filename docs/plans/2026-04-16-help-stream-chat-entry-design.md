# Help Stream Chat Entry Design

**Goal:** Remove the shared `/stream-chat` navigation entry and expose the chat page from the help page header card through a single `在线客服` button.

**Context**

- The shared top navigation currently exposes `/stream-chat` to all users.
- The help page already contains a prominent header card that can host a more context-appropriate support CTA.
- The requested change is limited to entry placement only. The `/stream-chat` route and page behavior remain unchanged.

**Recommended Approach**

1. Remove the `Stream Chat` link from the shared top navigation component.
2. Keep mobile navigation unchanged unless it also exposes `/stream-chat` publicly.
3. Add a single `在线客服` button inside the help page header card, linking to `/stream-chat`.
4. Update feature tests so navigation no longer expects the shared stream chat entry and the help page now expects the new CTA.

**Why This Approach**

- Smallest possible behavior change.
- Keeps support entry discoverable in a page that already matches the intent.
- Avoids changing routing, chat logic, or authorization in the same change set.

**Testing**

- Update global navigation assertions to ensure shared navigation no longer shows `Stream Chat` or `/stream-chat`.
- Update help page assertions to require `在线客服` and `/stream-chat`.
