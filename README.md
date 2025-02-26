# Automatizacion con php



### Enlaces
link: https://serviciosweb.afip.gob.ar/genericos/rg_17/Consulta.aspx

link: https://servicioscf.afip.gob.ar/Publico/rg830/rg830.aspx



### Diagrama de Flujo



<img src="./flujo proceso.png" alt="diagrama de flujo" width="300" />


crear una funcion que capture una url y que se contatene con una url(https://datospymes.produccion.gob.ar/api/resultadocondicion) y traiga una respuesta en json de esa url que consulta




nombre base de datos: certificados_aut

crear una tabla que tenga el cuit; nombre; nombre de archivo; fecha-procesamiento; fecha desde;  fecha hasta; porcentaje; si es cliente o no;

y crear un script que le agrege los datos que vienen de los archivos en esta tabla creada


'certificados', 'CREATE TABLE `certificados` 
(\n  `cuit` varchar(12) NOT NULL,\n  `nombre` varchar(255) NOT NULL,\n  `nombre_archivo` varchar(255) NOT NULL,\n  `fecha_procesamiento` datetime NOT NULL,\n  `fecha_desde` date NOT NULL,\n  `fecha_hasta` date NOT NULL,\n  `porcentaje` decimal(5,2) NOT NULL,\n  `es_cliente` tinyint(1) NOT NULL,\n  PRIMARY KEY (`cuit`)\n) ENGINE=MyISAM DEFAULT CHARSET=latin1'


cuit, nombre de archivo, fecha desde, fecha hasta


