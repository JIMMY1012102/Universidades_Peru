# Distribución del examen ordinario UNFV

Aplica únicamente a la opción **Villarreal** del selector de universidades (código interno C). UNDC, UNI y las demás opciones conservan sus formatos.

Fuente de las cuotas: [Prospecto de Admisión UNFV 2026](https://www.unfv.edu.pe/oca/images/pregrado/2026/ProspectoAdmision2026a.pdf), anexo 8, página impresa 70 (página 70 del PDF). Se usa la tabla de **examen ordinario**, no la del extraordinario ni el prospecto 2025. En particular, Humanidades tiene 4 preguntas de Literatura y 4 de Historia en 2026.

| Curso | Ingenierías y Arquitectura | Económicas, Financieras y Empresariales | Humanidades, Jurídicas y Sociales | Salud, Formales y Naturales |
|---|---:|---:|---:|---:|
| Habilidad Verbal | 7 | 7 | 7 | 7 |
| Habilidad Lógico Matemática | 7 | 7 | 7 | 7 |
| Aritmética | 3 | 2 | 1 | 2 |
| Geometría | 3 | 2 | 1 | 2 |
| Álgebra | 3 | 2 | 1 | 1 |
| Trigonometría | 2 | 1 | 0 | 1 |
| Física | 3 | 2 | 2 | 3 |
| Química | 3 | 2 | 1 | 3 |
| Biología | 3 | 2 | 2 | 5 |
| Lenguaje | 2 | 4 | 4 | 3 |
| Literatura | 2 | 2 | 4 | 2 |
| Psicología | 2 | 3 | 3 | 3 |
| Educación Cívica | 2 | 2 | 2 | 2 |
| Historia del Perú y Universal (cuota conjunta) | 2 | 3 | 4 | 3 |
| Geografía | 2 | 2 | 4 | 2 |
| Economía | 2 | 5 | 3 | 2 |
| Filosofía | 2 | 2 | 4 | 2 |
| **Total** | **50** | **50** | **50** | **50** |

## Uso

El alumno elige Villarreal, selecciona su área académica en el panel y pulsa **Dar examen**. Las peticiones antiguas sin perfil usan Ingenierías y Arquitectura. El servidor valida el perfil y determina las cuotas, el área permitida y el tiempo; ignora cantidades enviadas por el navegador.

Las nuevas plantillas UNFV tienen 90 minutos. Se conserva el sistema de puntaje existente: este cambio implementa la distribución, no la calificación oficial de la UNFV. Los perfiles del simulador siguen usando la autorización existente del código C; no se deduce el perfil UNFV de las antiguas carreras CNI.

## Banco e historial

Se reutilizan Razonamiento Verbal para Habilidad Verbal, Habilidad Matemática para Habilidad Lógico Matemática y el catálogo Filosofía y Lógica para Filosofía. La pertinencia temática depende del banco cargado: el cambio no convierte esas preguntas en preguntas oficiales de la UNFV.

Historia se sortea del conjunto de Historia del Perú e Historia Universal, con una sola cuota. No se inventa una división entre ambas. La fila persistida usa como referencia el primer curso de Historia activo disponible; el sorteo reconoce ambos. Humanidades no exige un curso ni preguntas de Trigonometría porque su cuota es cero.

Si falta un curso o preguntas válidas (activas y con cinco alternativas), se informa el motivo en el panel y se revierte la creación del intento y de la plantilla. No se sustituyen preguntas de otros cursos para completar 50.

Las plantillas incluyen el perfil y una versión derivada de sus cuotas y duración. Los intentos ya guardados no se modifican. Reanudar el mismo perfil conserva preguntas y hora límite.

## Actualización de Oracle

```bash
cd /var/www/universidades-peru
git pull --ff-only origin 'OFICIAL#1'
php8.3 artisan view:clear
```

No requiere migraciones, importación SQL ni volver a ejecutar seeders de usuarios.

Pruebas: `php artisan test --filter='ExamenUnfvTest|ExamenIndependienteTest|ExamenUniTest'`.
