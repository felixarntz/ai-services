# Entities

Directory: `includes/Services/Entities/`

This directory contains PHP classes that define entity representations and query mechanisms for AI services and their interaction histories. These entities are primarily designed for use with the WordPress REST API, providing a structured way to access and manage service-related data. The classes implement common entity interfaces provided by the `wp-oop-plugin-lib`, which is bundled and prefixed within the `third-party/` directory.

## Purpose

The primary purpose of the code within this directory is to:

- Define entity classes (`Service_Entity`, `History_Entity`) that encapsulate data related to AI services and their usage history, making it suitable for REST API responses.
- Provide query classes (`Service_Entity_Query`, `History_Entity_Query`) to fetch, filter, sort, and paginate collections of these entities.
- Abstract the underlying data sources and logic, such as service registration details from `includes/Services/Services_API.php` and history data from `includes/Services/API/History_Persistence.php`.
- Standardize data access for AI services and histories by implementing the `Entity` and `Entity_Query` contracts from the `wp-oop-plugin-lib`.

## Key Components

- **`Service_Entity.php`**: Represents an individual AI service. It provides access to the service's slug, metadata (retrieved via `includes/Services/API/Types/Service_Metadata.php`), availability, capabilities, and available models (defined in `includes/Services/API/Types/Model_Metadata.php`).
- **`Service_Entity_Query.php`**: Handles querying for `Service_Entity` objects. It allows filtering by slugs, ordering, and pagination. It interacts with `includes/Services/Services_API.php` to get registered service slugs.
- **`History_Entity.php`**: Represents a specific interaction history with an AI service for a particular feature. It exposes the feature, slug, last update time, and history entries (defined in `includes/Services/API/Types/History_Entry.php`). The underlying data comes from `includes/Services/API/Types/History.php`.
- **`History_Entity_Query.php`**: Handles querying for `History_Entity` objects. It supports filtering by feature and slugs, ordering by various fields (slug, created, lastUpdated), and pagination. It relies on `includes/Services/API/History_Persistence.php` to load history data.

## Architectural Considerations

- **REST API Focus**: These entities and queries are fundamental to the plugin's REST API, serving as the data transfer objects and retrieval logic for service and history-related endpoints.
- **Dependency on `wp-oop-plugin-lib`**: The use of `Entity` and `Entity_Query` interfaces from the (prefixed) `wp-oop-plugin-lib` (found in `third-party/`) ensures a consistent and reusable pattern for data entity management within the plugin.
- **Slug-based Identification**: Both services and histories primarily use slugs for identification rather than numeric IDs. This is reflected in the `get_id()` methods of the entity classes typically returning a placeholder value (e.g., `0`).
- **Data Abstraction**: The entity classes abstract the complexities of how service and history data is stored and retrieved, providing a clean interface for consumers (primarily the REST API controllers). For example, `Service_Entity` dynamically fetches live data about models and capabilities from the respective AI service via `includes/Services/Services_API.php`.
- **Interaction with Core Services**: These entities interact closely with other core components:
    - `includes/Services/Services_API.php`: For service registration, metadata, and availability.
    - `includes/Services/API/History_Persistence.php`: For loading and managing history data.
    - `includes/Services/API/Types/`: For various data structures like `Service_Metadata`, `Model_Metadata`, `History`, and `History_Entry`.
