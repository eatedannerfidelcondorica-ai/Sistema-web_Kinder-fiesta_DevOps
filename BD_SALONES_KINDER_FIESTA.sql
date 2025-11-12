CREATE DATABASE Salones;
USE Salones;

CREATE TABLE salon (
    id_salon INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    telefono CHAR(20) NOT NULL,
    direccion VARCHAR(200) NOT NULL,
    descripcion TEXT NOT NULL,
    latitud DECIMAL(10,8) NOT NULL,
    longitud DECIMAL(11,8) NOT NULL
);


CREATE TABLE foto (
    id_foto INT AUTO_INCREMENT PRIMARY KEY,
    id_salon INT NOT NULL,
    url_foto VARCHAR(255) NOT NULL,
    FOREIGN KEY (id_salon) REFERENCES salon(id_salon) ON DELETE CASCADE
);


CREATE TABLE calificacion (
    id_calificacion INT AUTO_INCREMENT PRIMARY KEY,
    id_salon INT NOT NULL,
    nombreUsuario VARCHAR(100) NOT NULL,
    comentario TEXT NOT NULL,
    estrellas INT CHECK(estrellas BETWEEN 1 AND 5),
    fechaRegistro DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_salon) REFERENCES salon(id_salon) ON DELETE CASCADE
);


CREATE TABLE administrador (
    id_adm INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) UNIQUE NOT NULL,
    clave VARCHAR(255) NOT NULL
);

INSERT INTO salon (nombre, telefono, direccion, descripcion, latitud, longitud) VALUES
('Hakuna Matata', '70693245', 'Av. Cívica n 55, El Alto','Un espacio temático lleno de aventuras, con juegos interactivos.',-16.5191254, -68.1569152),

('Zootopia', '75845848', 'Zn. Cosmos 79, C. Pachuni, El Alto','Salón inspirado en el mundo de Zootopia y con decoración colorida.', 
 -16.5311487, -68.2184594),

('Merlín', '65567153', 'Zn. Elizardo, C. Alejandro Perez','Un salón mágico que transporta a los niños a un mundo de fantasía con efectos especiales',-16.5344756, -68.1844804);

INSERT INTO foto (id_salon, url_foto) VALUES
(1,'salon1.jpg');
INSERT INTO foto (id_salon, url_foto) VALUES
(2,'salon2.jpg'),
(3,'salon3.jpg');
select * from foto;
-- UPDATE foto SET url_foto = 'salon.jpg' WHERE id_salon=3;

-- UPDATE salon SET descripcion = 'Un espacio temático lleno de aventuras, con juegos interactivos.' WHERE id_salon=1;
-- update salon set descripcion= 'Salón inspirado en el mundo de Zootopia y con decoración colorida.' WHERE id_salon=2;
-- update salon set descripcion= 'Un salón mágico que transporta a los niños a un mundo de fantasía con efectos especiales' WHERE id_salon=3;
select * from calificacion;