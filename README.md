# AtRest Ads Scheduler Plugin

## Setup Instructions

**Important:** Before using this plugin, create the following ACF fields in **Advertisement Settings**:

-   **`show_from`** - Date picker field
-   **`show_to`** - Date picker field

## How It Works

The advertisement visibility is automatically managed based on the `show_from` and `show_to` dates. In this mode, the "Is this banner currently live?" setting is ignored.

## Features

-   Automatically activates ads when current date is between `show_from` and `show_to`
-   Automatically deactivates ads outside the scheduled date range
-   Runs scheduled checks twice daily via WordPress cron
-   Updates ad status on save when dates are modified
