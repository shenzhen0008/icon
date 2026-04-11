# Help FAQ Page Design

## Goal

Add a public `/help` page as a lightweight FAQ skeleton and expose it from the primary desktop navigation.

## Scope

- Add a public help route and controller under `app/Modules/Help`
- Render a themed FAQ page with collapsible question items
- Add a `帮助` entry to the top navigation
- Keep the mobile bottom navigation unchanged for now to avoid overcrowding
- Cover the new page and navigation with feature tests

## Approach

The page will be a public Blade view using the existing top and mobile navigation components so it inherits both current themes. FAQ content will come from `config/help.php`, keeping copy centralized and avoiding magic strings in the controller.

The FAQ interaction will use native `details/summary` elements. That gives the desired click-to-expand / click-to-collapse behavior without introducing a second frontend system or custom JavaScript state management.

## UI Shape

- Title band: short heading and support copy
- FAQ list: repeated themed blocks inspired by the dense list feel of the home exchange metrics list
- Each item:
  - question row
  - expand/collapse indicator
  - answer body revealed when open

## Navigation

Add `帮助` to the desktop top navigation only. Mobile bottom nav remains at five entries:

- 首页
- 产品
- 我的
- 客服
- Stream

## Testing

- New feature test for `/help`
- Update global navigation test to assert `帮助` and `/help` are present on public pages and password confirm page
