use build_cache;
create table if not exists cache(id VARCHAR(500) NOT NULL, minified BOOLEAN NOT NULL DEFAULT 1, counter INT NOT NULL DEFAULT 0,  UNIQUE ID(id, minified) );\
delete from cache;
