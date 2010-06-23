CREATE TABLE blog_translation (id INTEGER, body LONGTEXT, lang CHAR(2), PRIMARY KEY(id, lang));
CREATE TABLE blog_slot (id INTEGER, blog_id INTEGER, PRIMARY KEY(id, blog_id));
CREATE TABLE blog (id INTEGER PRIMARY KEY AUTOINCREMENT, title VARCHAR(255));
CREATE TABLE sf_doctrine_slot (id INTEGER PRIMARY KEY AUTOINCREMENT, name VARCHAR(255), type VARCHAR(255), value LONGTEXT, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL);
