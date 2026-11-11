<?php

/*
 | Una sola plantilla de panel para las cinco áreas de postulación.
 | Los códigos A–E deben coincidir con areas_postulacion.codigo en MariaDB.
 | La carrera se consulta del perfil; nunca se asigna a partir de este mapa.
 */
return [
    'areas' => [
        'A' => [
            'titulo' => 'UNDC',
            'icono' => '✚',
            'descripcion' => 'Prepárate para ingresar a la Universidad Nacional de Cañete.',
            'lema' => 'Principio activo, innovador y humanista.',
            'mascota_png' => 'images/mascotas/UNDC_LOGO.png',
        ],
        'B' => [
            'titulo' => 'UNI',
            'icono' => '⚙',
            'descripcion' => 'Prepárate para ingresar a la Universidad Nacional de Ingeniería.',
            'lema' => 'Scientia et Labor: ciencia y trabajo para construir el Perú.',
            'mascota_png' => 'images/mascotas/UNI_logo.png',
        ],
        'C' => [
            'titulo' => 'Villarreal',
            'icono' => '⌘',
            'descripcion' => 'Prepárate para ingresar a la Universidad Nacional Federico Villarreal.',
            'lema' => 'Cada desafío es una oportunidad para avanzar.',
            'mascota_png' => 'images/mascotas/villareal_logo.png',
        ],
        'D' => [
            'icono' => '▥',
            'descripcion' => 'Desarrolla tus habilidades para liderar y emprender.',
            'lema' => 'Las grandes decisiones empiezan con una buena preparación.',
            'mascota_png' => 'images/mascotas/cni-mascota-economicas.png',
        ],
        'E' => [
            'titulo' => 'San Marcos',
            'icono' => '✦',
            'descripcion' => 'Prepárate para ingresar a la Universidad Nacional Mayor de San Marcos.',
            'lema' => 'Decana de América: la universidad más antigua del continente.',
            'mascota_png' => 'images/mascotas/San_Marcos_logo.png',
        ],
    ],
];
