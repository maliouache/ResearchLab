DROP TABLE PROJECT;
CREATE TABLE PROJECT(
    ID INTEGER PRIMARY KEY AUTO_INCREMENT,
    NAME VARCHAR(255),
    IMPORTANCE VARCHAR(15),
    DEADLINE VARCHAR(10),
    DOMAIN VARCHAR(255),
    COMMENT TEXT
);

INSERT INTO PROJECT(NAME,IMPORTANCE,DEADLINE,DOMAIN,COMMENT) VALUES ( First project, very-high, 12/05/2018, fluid-mechanics, c juste pour voir si la sauvegarde marche !!!)