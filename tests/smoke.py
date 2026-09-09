"""HTTP smoke test for a fresh seeded API; works against SQLite or MySQL."""
import json
import os
import time
import urllib.error
import urllib.request

base = os.environ.get("API_URL", "http://127.0.0.1:8080")


def request(path, expected=200, method="GET"):
    req = urllib.request.Request(base + path, method=method)
    try:
        response = urllib.request.urlopen(req, timeout=5)
    except urllib.error.HTTPError as error:
        response = error
    with response:
        assert response.status == expected, (path, response.status, expected)
        assert response.headers.get_content_type() == "application/json"
        return json.load(response)


for attempt in range(30):
    try:
        books = request("/books")
        break
    except (OSError, AssertionError):
        if attempt == 29:
            raise
        time.sleep(1)
assert len(books) == 4
assert request("/books/3") == {"id": 3, "book": "Treasure Island", "author_id": 2}
assert len(request("/authors")) == 3
assert request("/") == request("/authors")
assert [book["id"] for book in request("/authors/1/books")] == [1, 2]
assert request("/1/books") == request("/authors/1/books")
assert request("/books/999", 404)["error"]["message"] == "Book not found."
assert request("/authors/999/books", 404)["error"]["message"] == "Author not found."
for path in ["/books/abc", "/books/0", "/books/1.5", "/authors/-1/books"]:
    assert request(path, 400)["error"]["status"] == 400
assert request("/unknown/route", 404)["error"]["status"] == 404
assert request("/books", 405, "POST")["error"]["status"] == 405
print("HTTP smoke checks passed (lists, detail, filtering, aliases, validation, 404 and 405).")
