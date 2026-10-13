# Registro y autorizacion de evaluadores

La misma ruta `/register/evaluator` recibe las cuentas de los tres niveles. El registro crea el rol tecnico Evaluador y deja la cuenta pendiente. El formulario publico no permite enviar campos de autorizacion ni de nivel.

## Configuracion administrativa

En Administracion > Usuarios evaluadores, pulsar Configurar. Seleccionar Local, Estatal o Nacional y establecer el estado de autorizacion. Local requiere una institucion activa; Estatal requiere un estado. Nacional no necesita un ambito geografico adicional. Solo puede autorizarse una cuenta con correo verificado. El sistema registra quien la autorizo y la fecha. Pendiente o Suspendido bloquean el acceso a las rutas del evaluador.

La institucion de procedencia del registro no se modifica al asignar el ambito. Los evaluadores existentes quedan pendientes hasta que la administracion los configure.

## Restricciones

La consulta, el historial y el acceso a documentos requieren asignacion y ambito compatible. El servicio de asignaciones tambien valida la autorizacion, el ambito y que no se trate de la propia solicitud. Cambiar el ambito impide abrir asignaciones anteriores fuera del nuevo alcance; no elimina sus registros.

Esta entrega implementa el registro comun, la autorizacion y los ambitos. El flujo consecutivo Local > Estatal > Nacional y la emision del dictamen final Nacional requieren una entrega posterior; el dictamen administrativo y los estados actuales de las solicitudes se conservan.
