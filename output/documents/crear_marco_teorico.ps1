$ErrorActionPreference = 'Stop'
$out = Join-Path $PSScriptRoot 'Marco_teorico_Sistema_EDD_CENIDET.docx'
$pages = @(
@{
title='Marco teórico del Sistema EDD CENIDET'; sections=@(
@{h='Fundamentos del sistema web para la gestión de estímulos docentes'; p=@(
'El desarrollo del Sistema EDD CENIDET se sustenta en fundamentos de evaluación del desempeño docente, gestión de información e ingeniería de software. Estos enfoques justifican la organización digital de convocatorias, solicitudes, documentos y evaluaciones, así como la separación de responsabilidades entre docentes, evaluadores y administradores. El propósito del presente marco teórico es argumentar la relación entre dichos fundamentos y las decisiones del proyecto.',
'La propuesta corresponde a una aplicación web orientada al Programa de Estímulos al Desempeño del Personal Docente en el contexto del Tecnológico Nacional de México y del Centro Nacional de Investigación y Desarrollo Tecnológico. Su implementación integra Laravel, Vue e Inertia, junto con modelos para usuarios, convocatorias, solicitudes, documentos, rúbricas, evaluaciones y reconocimientos. Esta estructura permite analizar el proyecto como un sistema de apoyo a un proceso institucional, cuya calidad depende tanto de la organización del trabajo como de su implementación técnica.'
)},
@{h='1 Contexto institucional y objeto del proyecto'; p=@(
'El Tecnológico Nacional de México dispone de un programa institucional de estímulos docentes y de un portal para su operación (TecNM, s. f.). Este contexto respalda la pertinencia de estudiar herramientas que faciliten la gestión de la participación y de la evaluación. La aplicación propuesta debe apoyar el procedimiento definido por la institución y respetar la convocatoria que corresponda a cada periodo; las reglas de participación y las ponderaciones no deben deducirse únicamente del diseño de la plataforma.',
'El objeto del proyecto es la gestión de información asociada con dicho proceso. Un expediente reúne datos del participante, la convocatoria a la que pertenece, las evidencias presentadas y las valoraciones correspondientes. Por tanto, el sistema debe mantener relaciones consistentes entre estos elementos y permitir que cada usuario consulte o modifique exclusivamente la información autorizada.',
'La relevancia de la propuesta radica en proporcionar una estructura común para capturar, organizar y consultar los expedientes. Desde esta perspectiva, el trabajo práctico se justifica por la necesidad de articular funciones dispersas dentro de un mismo entorno. La reducción de tiempos, errores o cargas administrativas constituye una expectativa de investigación que requiere medición, y no un resultado que pueda afirmarse por la sola existencia del software.'
)})},
@{
title='2 Evaluación docente y reconocimiento profesional'; sections=@(
@{h='2.1 Evaluación del desempeño mediante evidencias'; p=@(
'Para este proyecto, el desempeño docente se entiende como el conjunto de actividades y resultados profesionales que pueden documentarse y valorarse conforme a criterios institucionales. Su evaluación exige establecer qué se observa, con qué evidencia se acredita y cómo se determina su valoración. Estas preguntas vinculan la evaluación con la gestión documental: una calificación debe poder relacionarse con el criterio aplicado y con los documentos que sustentan la decisión.',
'Las rúbricas explicitan criterios de evaluación y niveles de calidad del desempeño. Panadero y Jonsson (2013) revisan su utilización en la evaluación formativa y señalan su utilidad potencial para hacer comprensibles las expectativas de evaluación. Esta aportación fundamenta la necesidad de instrumentos definidos antes de valorar un expediente. Su traslado a la evaluación del personal docente requiere adaptación a los criterios institucionales y no permite asumir automáticamente los efectos observados en otros contextos educativos.',
'En la implementación, los modelos Rubric, RubricQuestion y RubricQuestionOption organizan los instrumentos, mientras que Evaluation contempla respuestas, puntuación, estado y comentario. Esta separación permite distinguir el instrumento utilizado de la valoración emitida. El argumento central es que conservar ambos elementos favorece la revisión del procedimiento y permite explicar de dónde procede una puntuación.',
'La consistencia de la evaluación depende también de la claridad de los criterios y de la preparación de los evaluadores. Digitalizar una rúbrica no elimina la subjetividad ni demuestra que el instrumento sea válido. Para sostener su calidad, deben revisarse la correspondencia entre criterios y objetivos, la claridad de las opciones y la coincidencia entre evaluadores ante expedientes comparables.'
)},
@{h='2.2 Teoría de la autodeterminación'; p=@(
'Ryan y Deci (2000) explican la motivación mediante las necesidades psicológicas de autonomía, competencia y relación con otras personas. Distinguen la motivación intrínseca de diferentes formas de motivación extrínseca y plantean que el contexto puede favorecer o limitar la autorregulación. Esta teoría ayuda a comprender que el reconocimiento profesional no depende exclusivamente de una recompensa, sino también de cómo se comunica y se interpreta el proceso de valoración.',
'En el Sistema EDD, esta perspectiva justifica ofrecer información clara sobre los requisitos y comentarios comprensibles sobre la evaluación. Conocer el avance de una solicitud y comprender las observaciones puede apoyar la participación informada. Sin embargo, el sistema no puede asegurar por sí mismo un aumento de la motivación docente: esa relación tendría que investigarse mediante instrumentos y observaciones en la población participante.'
)})},
@{
title='3 Gestión de información y organización de datos'; sections=@(
@{h='3.1 El sistema como apoyo al proceso institucional'; p=@(
'Un sistema de información articula personas, procedimientos, datos y recursos tecnológicos para producir información útil. En este proyecto, la información adquiere sentido dentro de un flujo de trabajo: publicación de la convocatoria, integración de la solicitud, presentación de documentos, evaluación y consulta de resultados. Esta definición permite identificar que la plataforma tiene una función organizativa además de una función informática.',
'El enfoque por procesos sustenta la necesidad de identificar entradas, actividades, responsables y salidas. Por ejemplo, una solicitud y sus evidencias constituyen entradas para la evaluación; el evaluador realiza la valoración y genera una puntuación y comentarios como salida. Esta lectura permite especificar qué información se requiere en cada etapa y qué cambios de estado deben admitirse.',
'La trazabilidad consiste en conservar relaciones que permitan reconstruir el origen y el recorrido de la información. En el proyecto, Application enlaza al usuario con la convocatoria y se relaciona con sus documentos y evaluaciones. Estos vínculos son una base para el seguimiento del expediente. Una trazabilidad completa exige además definir qué eventos deben registrarse, quién los ejecutó y en qué momento; la presencia de relaciones entre modelos no equivale por sí sola a una auditoría integral.'
)},
@{h='3.2 Modelo relacional e integridad de los datos'; p=@(
'Codd (1970) formuló el modelo relacional para representar los datos mediante relaciones y separar su organización lógica de determinadas formas de almacenamiento. Este fundamento permite estructurar entidades vinculadas y consultar información sin tratar cada expediente como un archivo aislado. En el proyecto, usuarios, convocatorias y solicitudes representan entidades diferentes que mantienen asociaciones explícitas.',
'Una solicitud pertenece a un usuario y a una convocatoria; sus documentos y evaluaciones remiten a la misma solicitud. Separar estas entidades permite evitar que los datos del participante se repitan innecesariamente en cada evidencia. Asimismo, facilita distinguir los datos generales de la convocatoria de la información particular de cada expediente.',
'La integridad referencial justifica utilizar claves y restricciones que impidan asociaciones inválidas, como una evaluación sin solicitud existente. La normalización orienta la reducción de redundancias, aunque su aplicación debe verificarse mediante el esquema de la base de datos. En consecuencia, la justificación de una base relacional se apoya en la coherencia de las relaciones y en la necesidad de conservar información consistente durante las operaciones del sistema.'
)})},
@{
title='4 Fundamentos de la arquitectura de software'; sections=@(
@{h='4.1 Separación de responsabilidades'; p=@(
'La separación de responsabilidades establece que componentes con funciones distintas deben organizarse de manera diferenciada. El patrón modelo vista controlador permite analizar esta distribución: los modelos representan datos y relaciones, los controladores coordinan las solicitudes y las vistas presentan información al usuario. Esta organización sustenta la mantenibilidad, porque una modificación visual no debería exigir reescribir las reglas de evaluación.',
'La estructura del proyecto contiene modelos en app/Models, controladores en app/Http/Controllers y páginas e interfaces en resources/js. Esta organización proporciona una correspondencia práctica con el principio de separación. El beneficio esperado es facilitar la localización de cambios y disminuir el acoplamiento entre la presentación de datos y la gestión de los expedientes.',
'Laravel constituye el soporte del procesamiento del servidor. Su documentación diferencia la autenticación, que identifica al usuario, de la autorización, que determina las acciones permitidas sobre recursos específicos (Laravel, s. f.). Esta distinción justifica que iniciar sesión no sea suficiente para acceder a cualquier solicitud o modificar una evaluación.'
)},
@{h='4.2 Componentes y presentación reactiva'; p=@(
'Vue permite construir interfaces declarativas a partir de componentes y un sistema de reactividad que actualiza la presentación cuando cambia el estado de los datos (Vue.js, s. f.). En el proyecto, esta característica resulta pertinente para formularios, navegación y visualización de solicitudes, donde el usuario necesita recibir información comprensible sobre sus acciones.',
'La reutilización de componentes fundamenta mantener una presentación coherente entre páginas. Los diseños LandingLayout y LayoutAuthenticated diferencian el entorno público del área autenticada, mientras que la configuración del tema concentra elementos de identidad visual. Esta distribución facilita mantener consistencia en navegación, tipografía y colores sin duplicar las mismas decisiones en cada pantalla.',
'Inertia integra las páginas del cliente con las rutas y respuestas del servidor. En esta arquitectura, Laravel conserva responsabilidades sobre el acceso a los datos y Vue presenta las páginas. La elección se justifica por la posibilidad de mantener un flujo de navegación integrado, aunque su calidad depende de que las respuestas, validaciones y permisos se definan correctamente.'
)},
@{h='4.3 Mantenibilidad y verificación'; p=@(
'Una estructura ordenada facilita el mantenimiento, pero no lo garantiza. La argumentación técnica debe complementarse con pruebas sobre reglas de negocio, permisos y relaciones de datos. Para este sistema, resulta especialmente relevante verificar que una operación de un docente no afecte expedientes ajenos y que las modificaciones de catálogos no alteren indebidamente evaluaciones ya realizadas. Estas comprobaciones conectan el diseño arquitectónico con comportamientos observables.'
)})},
@{
title='5 Seguridad y experiencia de uso'; sections=@(
@{h='5.1 Control de acceso basado en roles'; p=@(
'El control de acceso basado en roles, conocido como RBAC, asigna permisos a roles y relaciona a los usuarios con ellos. Según el NIST (s. f.), este enfoque organiza el acceso conforme a funciones dentro de una organización. Su pertinencia para el proyecto surge de que docentes, evaluadores y administradores requieren operaciones diferentes sobre los mismos procesos.',
'El Sistema EDD contempla roles y permisos mediante sus modelos y la dependencia spatie/laravel-permission. Esta estructura permite delimitar acciones como administrar convocatorias, presentar solicitudes o realizar evaluaciones. El principio de mínimo privilegio orienta conceder únicamente las operaciones necesarias para la responsabilidad asignada. Ocultar una opción del menú mejora la navegación, pero el permiso también debe comprobarse en el servidor.',
'La seguridad exige verificar tanto la función del usuario como su relación con el recurso solicitado. Por ejemplo, dos docentes pueden compartir el mismo rol sin que deban consultar mutuamente sus documentos. Por ello, la autorización debe considerar la propiedad del expediente, además del permiso general asociado al rol.'
)},
@{h='5.2 Protección y disponibilidad de la información'; p=@(
'El estándar de verificación de seguridad de aplicaciones de OWASP proporciona requisitos para evaluar controles técnicos de seguridad (OWASP Foundation, s. f.). Este referente permite traducir preocupaciones generales en comprobaciones de autenticación, autorización, validación y manejo de información. Su adopción como guía no implica que el proyecto esté certificado ni que todos sus controles hayan sido auditados.',
'En el sistema, los documentos de los participantes requieren controles sobre carga, almacenamiento y descarga. Los respaldos existentes en la estructura del proyecto apoyan la continuidad operativa, pero su eficacia depende de verificar la recuperación de información. De este modo, la seguridad se argumenta como una propiedad transversal que debe acompañar las funciones del expediente.'
)},
@{h='5.3 Usabilidad y accesibilidad'; p=@(
'La usabilidad se vincula con la posibilidad de completar tareas con claridad y esfuerzo razonable. Para este proyecto, implica comprender dónde presentar una solicitud, qué documentos faltan y qué significan los estados del trámite. La consistencia del diseño y los mensajes de validación deben contribuir a estas tareas, además de mejorar la apariencia.',
'Las WCAG 2.2 organizan la accesibilidad en los principios de contenido perceptible, operable, comprensible y robusto (W3C, 2024). Estos principios justifican revisar el contraste, la navegación mediante teclado, las etiquetas de formularios y la comunicación de errores. La opción de modo oscuro puede mejorar una preferencia visual, pero su existencia no demuestra conformidad con las pautas de accesibilidad.'
)})},
@{
title='6 Argumentación de la propuesta de investigación'; sections=@(
@{h='6.1 Relación entre fundamentos y trabajo práctico'; p=@(
'Los fundamentos expuestos permiten justificar la propuesta como un sistema de apoyo a la gestión y evaluación de expedientes docentes. La evaluación mediante criterios explica la existencia de rúbricas y evidencias; el modelo relacional sustenta los vínculos entre solicitudes, documentos y evaluaciones; la separación de responsabilidades organiza la implementación; y el control por roles delimita la intervención de cada participante.',
'La relación entre teoría y práctica debe reflejarse en requisitos verificables. Si se pretende apoyar una evaluación explicable, deben conservarse el instrumento aplicado, las respuestas y la relación con el expediente. Si se busca coherencia de datos, deben probarse las restricciones y las operaciones sobre entidades relacionadas. Si se pretende una interacción accesible, deben observarse las tareas de los usuarios y comprobarse los criterios pertinentes de las WCAG.',
'El aporte del proyecto consiste en articular estos principios dentro de una aplicación vinculada con un proceso institucional concreto. Su valor académico se encuentra en justificar decisiones de diseño, implementar mecanismos acordes con ellas y evaluar si el comportamiento obtenido responde a las necesidades identificadas. La selección de tecnologías es parte del trabajo, pero necesita acompañarse de argumentos sobre su adecuación al problema.'
)},
@{h='6.2 Aspectos que permiten contrastar la propuesta'; p=@(
'Una pregunta pertinente para orientar la investigación es cómo una plataforma web con expedientes relacionados, criterios de evaluación y permisos por rol puede apoyar la organización del proceso de estímulos docentes. Para responderla, conviene observar tareas específicas y establecer una referencia previa con la cual comparar el funcionamiento de la propuesta.',
'La organización de información puede examinarse mediante la facilidad para localizar un expediente y la presencia de documentos vinculados correctamente. La consistencia puede evaluarse con casos que intenten producir relaciones inválidas. La seguridad debe comprobarse con operaciones permitidas y denegadas por rol y por propiedad del recurso. La experiencia de uso puede estudiarse mediante el cumplimiento de tareas, los errores encontrados y la percepción de claridad de los participantes.',
'Estos criterios permiten distinguir los resultados de las expectativas. Un tiempo de atención menor, una mejor comprensión del procedimiento o una disminución de errores deben sustentarse con datos obtenidos en la evaluación del sistema. De igual manera, cualquier conclusión sobre motivación docente requiere un estudio específico y no puede inferirse únicamente a partir del uso de la plataforma.'
)},
@{h='6.3 Síntesis del marco teórico'; p=@(
'El Sistema EDD CENIDET se fundamenta en la articulación de evaluación documentada, organización de datos, arquitectura mantenible y acceso controlado. Estos enfoques amparan el trabajo práctico al explicar por qué es necesario relacionar evidencias con criterios, diferenciar responsabilidades y facilitar una interacción comprensible. La validez de la propuesta deberá sostenerse mediante pruebas y observaciones que confirmen su utilidad dentro del proceso institucional.'
)})},
@{
title='Referencias'; sections=@(
@{h='Fuentes académicas e institucionales'; p=@(
'Codd, E. F. (1970). A relational model of data for large shared data banks. Communications of the ACM, 13(6), 377–387. https://doi.org/10.1145/362384.362685',
'Laravel. (s. f.). Authorization. Laravel 12.x documentation. https://laravel.com/docs/12.x/authorization',
'National Institute of Standards and Technology. (s. f.). Role based access control FAQs. https://csrc.nist.gov/Projects/role-based-access-control/faqs',
'OWASP Foundation. (s. f.). Application Security Verification Standard. https://owasp.org/projects/asvs',
'Panadero, E., & Jonsson, A. (2013). The use of scoring rubrics for formative assessment purposes revisited: A review. Educational Research Review, 9, 129–144. https://doi.org/10.1016/j.edurev.2013.01.002',
'Ryan, R. M., & Deci, E. L. (2000). Self-determination theory and the facilitation of intrinsic motivation, social development, and well-being. American Psychologist, 55(1), 68–78. https://doi.org/10.1037/0003-066X.55.1.68',
'Tecnológico Nacional de México. (s. f.). Programa de Estímulos al Desempeño del Personal Docente. https://edd.tecnm.mx/docentes/index.php',
'Vue.js. (s. f.). Introduction. https://vuejs.org/guide/introduction.html',
'World Wide Web Consortium. (2024). Web Content Accessibility Guidelines WCAG 2.2. https://www.w3.org/TR/2024/REC-WCAG22-20241212/'
)})}
)

# Build a portable OOXML document without requiring a package installation.
Add-Type -AssemblyName System.IO.Compression.FileSystem
Add-Type -AssemblyName System.IO.Compression
function EscapeXml([string]$s) { [System.Security.SecurityElement]::Escape($s) }
function Para([string]$t,[string]$style='Normal') {
 '<w:p><w:pPr><w:pStyle w:val="'+$style+'"/></w:pPr><w:r><w:t xml:space="preserve">'+(EscapeXml $t)+'</w:t></w:r></w:p>'
}
$body = ''
for($i=0;$i -lt $pages.Count;$i++) {
 if($i -gt 0){$body += '<w:p><w:r><w:br w:type="page"/></w:r></w:p>'}
 $body += Para $pages[$i].title $(if($i -eq 0){'Title'}else{'Heading1'})
 foreach($s in $pages[$i].sections){$body += Para $s.h 'Heading2'; foreach($p in $s.p){$body += Para $p $(if($i -eq 6){'Reference'}else{'Normal'})}}
}
$ns='xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"'
$doc='<?xml version="1.0" encoding="UTF-8" standalone="yes"?><w:document '+$ns+'><w:body>'+$body+'<w:sectPr><w:pgSz w:w="12240" w:h="15840"/><w:pgMar w:top="1080" w:right="1080" w:bottom="1080" w:left="1080"/><w:footerReference w:type="default" r:id="rId2" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"/></w:sectPr></w:body></w:document>'
$styles='<?xml version="1.0" encoding="UTF-8"?><w:styles '+$ns+'><w:docDefaults><w:rPrDefault><w:rPr><w:rFonts w:ascii="Calibri" w:hAnsi="Calibri"/><w:sz w:val="23"/><w:color w:val="000000"/><w:lang w:val="es-MX"/></w:rPr></w:rPrDefault><w:pPrDefault><w:pPr><w:spacing w:after="120" w:line="285" w:lineRule="auto"/><w:widowControl/></w:pPr></w:pPrDefault></w:docDefaults><w:style w:type="paragraph" w:default="1" w:styleId="Normal"><w:name w:val="Normal"/></w:style><w:style w:type="paragraph" w:styleId="Title"><w:name w:val="Title"/><w:basedOn w:val="Normal"/><w:pPr><w:keepNext/><w:spacing w:after="220"/></w:pPr><w:rPr><w:b/><w:sz w:val="38"/><w:color w:val="000000"/></w:rPr></w:style><w:style w:type="paragraph" w:styleId="Heading1"><w:name w:val="heading 1"/><w:basedOn w:val="Normal"/><w:pPr><w:keepNext/><w:spacing w:after="180"/><w:outlineLvl w:val="0"/></w:pPr><w:rPr><w:b/><w:sz w:val="30"/><w:color w:val="000000"/></w:rPr></w:style><w:style w:type="paragraph" w:styleId="Heading2"><w:name w:val="heading 2"/><w:basedOn w:val="Normal"/><w:pPr><w:keepNext/><w:spacing w:before="180" w:after="100"/><w:outlineLvl w:val="1"/></w:pPr><w:rPr><w:b/><w:sz w:val="25"/><w:color w:val="000000"/></w:rPr></w:style><w:style w:type="paragraph" w:styleId="Reference"><w:name w:val="Reference"/><w:basedOn w:val="Normal"/><w:pPr><w:ind w:left="360" w:hanging="360"/><w:spacing w:after="180"/></w:pPr></w:style></w:styles>'
$entries=@{
'[Content_Types].xml'='<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/><Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/><Override PartName="/word/footer1.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.footer+xml"/></Types>'
'_rels/.rels'='<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/></Relationships>'
'word/document.xml'=$doc
'word/styles.xml'=$styles
'word/_rels/document.xml.rels'='<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/footer" Target="footer1.xml"/></Relationships>'
'word/footer1.xml'='<w:ftr '+$ns+'><w:p><w:pPr><w:jc w:val="center"/></w:pPr><w:r><w:rPr><w:sz w:val="20"/></w:rPr><w:fldChar w:fldCharType="begin"/></w:r><w:r><w:instrText> PAGE </w:instrText></w:r><w:r><w:fldChar w:fldCharType="end"/></w:r></w:p></w:ftr>'
}
$zip=[System.IO.Compression.ZipFile]::Open($out,[System.IO.Compression.ZipArchiveMode]::Create)
try {foreach($name in $entries.Keys){$entry=$zip.CreateEntry($name);$writer=[System.IO.StreamWriter]::new($entry.Open(),[System.Text.UTF8Encoding]::new($false));$writer.Write($entries[$name]);$writer.Dispose()}}finally{$zip.Dispose()}
Write-Output $out
