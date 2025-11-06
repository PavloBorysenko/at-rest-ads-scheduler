# AtRest Ads Scheduler Plugin

## Setup Instructions

**Important:** Before using this plugin, create the following ACF fields in **Advertisement Settings**:

-   **`is_scheduled`** - True/False field
-   **`show_from`** - Date picker field
-   **`show_to`** - Date picker field

## How It Works

### Manual Mode (is_scheduled: OFF)

When the `is_scheduled` checkbox is **disabled**, the advertisement visibility is controlled manually by the "Is this banner currently live?" setting.

### Automatic Scheduling Mode (is_scheduled: ON)

When the `is_scheduled` checkbox is **enabled**, the advertisement visibility is automatically managed based on the `show_from` and `show_to` dates. In this mode, the "Is this banner currently live?" setting is ignored.

## Features

-   Automatically activates ads when current date is between `show_from` and `show_to`
-   Automatically deactivates ads outside the scheduled date range
-   Runs scheduled checks twice daily via WordPress cron
-   Updates ad status on save when dates are modified
