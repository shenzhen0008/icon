# Orders Page Design

## Context

The project already has a "持仓产品" panel in the personal center at `/me`, implemented by the reusable Blade component `resources/views/components/me/positions-panel.blade.php`. Users can also open a single order detail page at `/me/positions/{position}`.

The new requirement is to add a dedicated orders page at `/me/orders` and place the same holding product cards from the personal center onto that page.

## Recommended Approach

Add a new authenticated page at `/me/orders` that renders the existing positions panel component. Keep the visual structure aligned with the current user-facing pages, and avoid duplicating the holding-card markup.

To keep controller responsibilities narrow, move the user position list shaping into a Position module service. The service will return the same data structure currently consumed by the positions panel:

- position id
- product name
- formatted principal
- current status
- latest three daily profit rows

This service can then be used both by the new orders page controller and by the existing personal center controller.

## Alternatives Considered

### 1. Duplicate the Blade panel in a new orders page

This is the fastest short-term path, but it creates avoidable template drift and doubles future maintenance cost.

### 2. Add the orders page but keep the data assembly inside each controller

This keeps file count smaller but violates the local layering rules for new work and repeats the same query and mapping logic.

## Design Details

### Route and Access

- Add `GET /me/orders`
- Protect it with the existing `auth` middleware group

### Controller

- Add a Position module controller dedicated to the orders page
- The controller only requests the prepared position list from a service and returns a view

### Service

- Add a Position module service that loads the authenticated user's `open` and `redeeming` positions
- Eager load related products
- Fetch recent settlements for those positions
- Return a normalized array for the panel view

### View

- Add a dedicated orders index page
- Use the existing page shell pattern with top nav, background glow, mobile nav, and unified container width
- Render a page title and reuse `<x-me.positions-panel :positions="$positions" />`

### Testing

Add feature tests for:

- authenticated user can view `/me/orders`
- guest cannot view `/me/orders`
- page shows `open` and `redeeming` positions while hiding `redeemed`

## Success Criteria

- `/me/orders` is reachable for authenticated users
- the page reuses the existing holding product panel
- guest access is denied by auth middleware
- only active/redeeming holdings appear
