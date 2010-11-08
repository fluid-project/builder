drop table if exists cache;
create table cache(id VARCHAR(500) NOT NULL, minified BOOLEAN NOT NULL DEFAULT 1, counter INT NOT NULL DEFAULT 0,  UNIQUE ID(id, minified) );
