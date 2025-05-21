# HTTP Handling with Streaming Support

Directory: `includes/Services/HTTP/`

This directory contains classes and interfaces responsible for handling HTTP requests, with a particular focus on supporting streamed responses. This is essential for interacting with AI services that may return large amounts of data incrementally.

## Purpose

The primary purpose of the code within this directory is to:

- Provide a reliable mechanism for making HTTP requests that can efficiently process streamed data. This capability is crucial for features like real-time text generation from AI models.

## Key Components

- **`HTTP_With_Streams.php`**: This class extends the base `HTTP` class (from the `wp-oop-plugin-lib` dependency, found in `third-party/`) to add support for streaming HTTP responses. It utilizes the GuzzleHttp client for streaming capabilities, as the standard WordPress HTTP API does not offer this functionality. This class implements the `Stream_Request_Handler` interface.

- **`Stream_Response.php`**: This class represents an HTTP response where the body is a stream. It implements the `With_Stream` interface and `IteratorAggregate`, allowing the streamed response data (expected to be JSON chunks) to be iterated over. This is useful for processing parts of a large response as they arrive, rather than waiting for the entire response.

## Subdirectories

### `Contracts/`

Directory: `includes/Services/HTTP/Contracts/`

This subdirectory contains PHP interfaces that define the contracts for HTTP stream handling:

- **`Stream_Request_Handler.php`**: Defines the contract for any class that needs to send an HTTP request and handle a streamed response. The `HTTP_With_Streams` class implements this interface.
- **`With_Stream.php`**: Defines the contract for any class that encapsulates a readable stream, providing a method to read data from that stream. The `Stream_Response` class implements this interface.

## Architecture and Technical Decisions

- **GuzzleHttp for Streaming**: A key technical decision was to use the GuzzleHttp client for handling streamed responses. This was necessary because the WordPress Core HTTP API (WP_Http) does not natively support response streaming.
- **JSON Stream Processing**: The `Stream_Response` class is designed to read and parse JSON objects from a continuous stream. This is a common pattern for AI services that stream structured data.
- **Interface-Driven Design**: The use of interfaces in the `Contracts/` subdirectory promotes a decoupled design, allowing for different implementations of stream handling or response processing if needed in the future.

This directory plays a vital role in enabling efficient communication with AI services, particularly those that provide responses as a stream of data.
