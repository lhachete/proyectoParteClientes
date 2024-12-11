DELIMITER //
/* Eliminar tablas si ya existen */
DROP TABLE IF EXISTS client CASCADE;

/*Creación de la tabla client */
CREATE TABLE client (
                        clientuuid VARCHAR(36),
                        clientname VARCHAR(100) NOT NULL,
                        clientaddress VARCHAR(255),
                        clientisopen BOOLEAN NOT NULL,
                        clientcost DECIMAL(10,2),
                        useruuid VARCHAR(36),
                        PRIMARY KEY (clientuuid)

);
//
