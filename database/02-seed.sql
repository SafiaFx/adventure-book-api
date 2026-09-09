-- Small demonstration catalogue; titles and author names only.
INSERT INTO Authors (id, author) VALUES
    (1, 'Jules Verne'),
    (2, 'Robert Louis Stevenson'),
    (3, 'Jack London');
INSERT INTO Books (id, book, author_id) VALUES
    (1, 'Around the World in Eighty Days', 1),
    (2, 'Journey to the Centre of the Earth', 1),
    (3, 'Treasure Island', 2),
    (4, 'The Call of the Wild', 3);
