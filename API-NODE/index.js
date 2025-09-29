const express = require('express');
const sql = require('mssql');
const cors = require('cors');
const crypto = require('crypto');
const logger = require('./logger');

const PORT = process.env.PORT || 3000;
let config;

// Configuración de la conexión a la base de datos
const local = false;
if (local) {
  config = {
    user: 'sa',
    password: 'Sp_admon',
    server: 'localhost',
    database: 'CEA',
    options: {
      encrypt: false,
      trustServerCertificate: false,
      charset: 'UTF8',
    },
  };
} else {
  config = {
    user: 'systemdiaapp',
    password: 'Sql1234.',
    server: '192.168.100.22',
    database: 'CEA',
    options: {
      encrypt: false,
      trustServerCertificate: false,
      charset: 'UTF8',
    },
  };
}
const app = express();

//Directorio Público
app.use(express.static('public'));
app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

sql.connect(config, function (err) {
  if (err) console.log(err);
  else console.log('Conexion exitosa');
});

sql.on('connect', (connection) => {
  connection.query("SET NAMES 'utf8'");
});

// Configurar la respuesta OPTIONS para todas las rutas
app.options('*', cors());

//------------- CERTIFICADOS CEA  ----------------------
app.post('/login', async (req, res) => {
  const { strLoginEmpresa, strPassword } = req.body;
  try {
    const pool = await sql.connect(config);
    const result = await pool
      .request()
      .input('strLoginEmpresa', sql.NVarChar, strLoginEmpresa)
      .input('strPassword', sql.NVarChar, strPassword)
      .execute('SP_LoginEmpresa');

    if (result.recordset.length > 0) {
      res.json(result.recordset[0]);
    } else {
      res.status(401).send('Credenciales invalidas');
    }
  } catch (error) {
    console.error('Error al ejecutar la consulta:', error);
    res.status(500).send('Error al autenticar el usuario');
  } finally {
    sql.close();
  }
});

// Ruta para actualizar la contraseña de la empresa logueada
app.post('/update-password', async (req, res) => {
  const { strLoginEmpresa, strNewPassword } = req.body;
  try {
    const pool = await sql.connect(config);
    await pool
      .request()
      .input('strLoginEmpresa', sql.NVarChar, strLoginEmpresa)
      .input('strNewPassword', sql.NVarChar, strNewPassword)
      .execute('SP_UpdatePassword');

    res.status(200).send('Contraseña actualizada con éxito');
  } catch (error) {
    console.error('Error al actualizar la contraseña:', error);
    res.status(500).send('Error al actualizar la contraseña de la empresa');
  } finally {
    sql.close();
  }
});

//------------------------------------

//-------------- EXAMENES ------------
app.post('/examen', async (req, res) => {
  const empresa = req.body.empresa;
  try {
    const pool = await sql.connect(config);

    const result = await pool
      .request()
      .input('strEmpresa', sql.NVarChar, empresa)
      .execute('SP_ExamenesXEmpresa');

    if (result.recordset.length > 0) {
      // Registros de examen encontrados
      res.json(result.recordset);
    } else {
      // No se encontraron registros de examen
      res
        .status(404)
        .send(
          'No se encontraron registros de examen para la empresa especificada'
        );
    }
  } catch (error) {
    console.error('Error al ejecutar la consulta:', error);
    res.status(500).send('Error al obtener los registros de examen');
  } finally {
    sql.close();
  }
});

//----------------CERTIFICADO ------------
app.post('/certificado', async (req, res) => {
  const id = req.body.id;
  try {
    const pool = await sql.connect(config);
    const result = await pool
      .request()
      .input('id', sql.Int, id)
      .execute('SP_CertificadoXId');

    if (result.recordset.length > 0) {
      res.json(result.recordset);
    } else {
      res.status(404).send('No se encontraron registros');
    }
  } catch (error) {
    console.error('Error al ejecutar la consulta:', error);
    res.status(500).send('Error al obtener los registros');
  }
});

//--------------------------------CIERRES DE CLASES  -------------
//--vehiculos
app.get('/vehiculos', async (req, res) => {
  try {
    const pool = await sql.connect(config);
    const result = await pool.request().query('SELECT * FROM Vis_Vehiculos');

    if (result.recordset.length > 0) {
      console.log(`\nVehiculos: `);
      // Para debugear si tiene elementos, solo mostrar los primeros 2
      for (let i = 0; i < Math.min(2, result.recordset.length); i++) {
        console.log(`${Object.values(result.recordset[i])[0]}`);
      }
    }
    res.json(result.recordset);
  } catch (error) {
    console.error('Error al obtener los vehículos:', error);
    res.status(500).send('Error al obtener los vehículos');
  } finally {
    sql.close();
  }
});

//-tipo de calses
app.get('/tipo-clases', async (req, res) => {
  try {
    const pool = await sql.connect(config);
    const result = await pool
      .request()
      .query('SELECT * FROM Esc_TipoClases WHERE bolEstado = 1');

    if (result.recordset.length > 0) {
      console.log(`\nTipos de clases: `);
      // Para debugear si tiene elementos, solo mostrar los primeros 2
      for (let i = 0; i < Math.min(2, result.recordset.length); i++) {
        console.log(`${Object.values(result.recordset[i])[1]}`);
      }

      // Responder con los tipos de clases obtenidos
      res.json(result.recordset);
    } else {
      res.status(404).send('No se encontraron tipos de clases');
    }
  } catch (error) {
    console.error('Error al ejecutar la consulta:', error);
    res.status(500).send('Error al obtener los tipos de claseS');
  } finally {
    sql.close();
  }
});

//LISTAR  cierres
app.get('/cierres', async (req, res) => {
  const strTutor = req.query.strTutor; // Leer ?strUsuario=juan o undefined

  try {
    const pool = await sql.connect(config);
    const request = pool.request();

    let query = 'SELECT * FROM Vis_Cierres ';

    if (strTutor) {
      query += ' WHERE strTutor = @strTutor';
      request.input('strTutor', sql.VarChar, strTutor);
    }
    query += ' ORDER BY dteLog DESC, dtefecha DESC'; // Ordenar por fecha de cierre

    const result = await request.query(query);

    if (result.recordset.length > 0) {
      res.json(result.recordset);
    } else {
      res
        .status(404)
        .send(
          strUsuario
            ? 'No se encontraron clases para el tutor especificado'
            : 'No se encontraron clases'
        );
    }
  } catch (error) {
    console.error('Error al ejecutar la consulta:', error);
    res.status(500).send('Error al obtener las clases');
  } finally {
    await sql.close();
  }
});

app.get('/cierre/:id', async (req, res) => {
  const id = parseInt(req.params.id);
  if (isNaN(id)) {
    return res.status(400).send('ID inválido');
  }
  try {
    const pool = await sql.connect(config);
    const result = await pool
      .request()
      .input('intIDCierre', sql.Int, id)
      .query('SELECT * FROM Esc_Cierres WHERE intIDCierre = @intIDCierre');

    if (result.recordset.length === 0) {
      return res.status(404).send('Cierre no encontrado');
    }

    res.json(result.recordset[0]);
  } catch (error) {
    console.error(error);
    res.status(500).send('Error al obtener cierre');
  } finally {
    sql.close();
  }
});

//--insertar cierre de clase
app.post('/insertar-cierre', async (req, res) => {
  const {
    dteFecha,
    intTipoClase,
    strTutor,
    strVehiculo,
    tmeHoraInicio,
    tmeHoraFin,
    intCantHoras,
    intCantMinutos,
  } = req.body;

  //console.log('Datos recibidos:', req.body);

  try {
    // 🔹 Forzar la fecha a YYYY-MM-DD para evitar desfase
    const fechaSQL = new Date(dteFecha).toISOString().split('T')[0];

    const pool = await sql.connect(config);
    await pool
      .request()
      .input('dteFecha', sql.Date, dteFecha)
      .input('intTipoClase', sql.Int, intTipoClase)
      .input('strTutor', sql.VarChar, strTutor)
      .input('strVehiculo', sql.VarChar, strVehiculo)
      .input('tmeHoraInicio', sql.VarChar(8), tmeHoraInicio)
      .input('tmeHoraFin', sql.VarChar(8), tmeHoraFin)
      .input('intCantHoras', sql.Int, intCantHoras)
      .input('intCantMinutos', sql.Int, intCantMinutos)
      .execute('SP_InsertarCierre');

       logger.info(`Cierre insertado con éxito: ${JSON.stringify(req.body)}`);
       res.status(200).send('Cierre insertado con éxito');

  } catch (error) {
       logger.error(`Error al insertar el Cierre: ${error.message}`);
       res.status(500).send('Error al insertar el Cierre');
  } finally {
    sql.close();
  }
});

//--editar cierre de clase
app.post('/editar-cierre', async (req, res) => {
  const {
    intIDCierre,
    dteFecha,
    intTipoClase,
    strTutor,
    strVehiculo,
    tmeHoraInicio,
    tmeHoraFin,
    intCantHoras,
    intCantMinutos,
    strObservaciones,
  } = req.body;

  try {
    const pool = await sql.connect(config);
    await pool
      .request()
      .input('intIDCierre', sql.Int, intIDCierre) // 👈 clave primaria para identificar el registro
      .input('dteFecha', sql.DateTime, dteFecha)
      .input('intTipoClase', sql.Int, intTipoClase)
      .input('strTutor', sql.VarChar, strTutor)
      .input('strVehiculo', sql.VarChar, strVehiculo)
      .input('tmeHoraInicio', sql.VarChar(8), tmeHoraInicio)
      .input('tmeHoraFin', sql.VarChar(8), tmeHoraFin)
      .input('intCantHoras', sql.Int, intCantHoras)
      .input('intCantMinutos', sql.Int, intCantMinutos)
      .input('strObservaciones', sql.VarChar, strObservaciones)
      .execute('SP_ActualizarCierre'); // 👈 asegúrate de tener este procedimiento almacenado

    console.log('Cierre actualizado con éxito\n', req.body);

    res.status(200).send('Cierre actualizado con éxito');
  } catch (error) {
    console.error('Error al actualizar el Cierre:', error);
    res.status(500).send('Error al actualizar el Cierre');
  } finally {
    sql.close();
  }
});

//-- eliminar cierre
app.delete('/eliminar-cierre/:id', async (req, res) => {
  const id = parseInt(req.params.id);
  if (isNaN(id)) {
    return res.status(400).send('ID inválido');
  }
  try {
    const pool = await sql.connect(config);
    const result = await pool
      .request()
      .input('intIDCierre', sql.Int, id)
      .query('DELETE FROM Esc_Cierres WHERE intIDCierre = @intIDCierre');

    if (result.rowsAffected[0] === 0) {
      return res.status(404).send('Cierre no encontrado');
    }

    res.status(200).send('Cierre eliminado con éxito');
  } catch (error) {
    console.error(error);
    res.status(500).send('Error al eliminar cierre');
  } finally {
    sql.close();
  }
});

app.get('/tutores', async (req, res) => {
  const strTutor = req.query.strTutor; // Leer ?strTutor=juan o undefined

  try {
    const pool = await sql.connect(config);
    const request = pool.request();

    let query = 'SELECT * FROM Vis_Tutores ';

    if (strTutor) {
      query += ' WHERE strTutor = @strTutor';
      request.input('strTutor', sql.VarChar, strTutor);
    }
    query += ' ORDER BY strNombreTutor DESC';

    const result = await request.query(query);

    if (result.recordset.length > 0) {
      res.json(result.recordset);
    } else {
      res
        .status(404)
        .send(
          strTutor
            ? 'No se encontraron clases para el tutor especificado'
            : 'No se encontraron clases'
        );
    }
  } catch (error) {
    console.error('Error al ejecutar la consulta:', error);
    res.status(500).send('Error al obtener las clases');
  } finally {
    await sql.close();
  }
});

//--LOGIN TUTOR con hash de C#
app.post('/loginTutor', async (req, res) => {
  const { strTutor, strPassword } = req.body;

  try {
    const pool = await sql.connect(config);
    const request = pool.request();

    // Ejecutar SP
    const result = await request
      .input('strTutor', sql.VarChar, strTutor)
      .execute('SP_LoginTutor');

    if (result.recordset.length === 0) {
      return res.status(401).send('Credenciales inválidas1');
    }

    const user = result.recordset[0];

    // Verificar la contraseña con el mismo esquema que C#
    if (!verifyHash(strTutor, strPassword, user.strPassword)) {
      return res.status(401).send('Credenciales inválidas2');
    }

    // Login correcto, eliminar contraseña antes de enviar
    delete user.strPassword;
    res.json(user);
  } catch (error) {
    console.error('Error en /loginTutor:', error);
    res.status(500).send('Error de autenticación');
  } finally {
    await sql.close();
  }
});

/*
app.get('/clases', async (req, res) => {
  const strTutor = req.query.strTutor;  // Leer de URL ?strTutor=juan

  try {
    const pool = await sql.connect(config);
    const result = await pool
      .request()
      .input('strTutor', sql.VarChar, strTutor)
      .execute('SP_ListarClasesXTutor');

    if (result.recordset.length > 0) {
      res.json(result.recordset);
    } else {
      res.status(404).send('No se encontraron clases para el tutor especificado');
    }
  } catch (error) {
    console.error('Error al ejecutar la consulta:', error);
    res.status(500).send('Error al obtener las clases del tutor');
  } finally {
    sql.close();
  }
});
*/

//--Respuestas de las peticiones
app.use((req, res, next) => {
  console.log(`Petición: ${req.method} ${req.url}`);
  next();
});

// Función que imita la encriptación usada en el escritorio
function verifyHash(strTutor, strClave, storedHash) {
  const plainText = strTutor + strClave;

  // Decodificar el hash guardado (hash+salt en Base64)
  const hashWithSaltBytes = Buffer.from(storedHash, 'base64');

  // SHA512 = 512 bits = 64 bytes
  const hashSizeInBytes = 64;

  if (hashWithSaltBytes.length < hashSizeInBytes) {
    return false; // hash corrupto
  }

  // Extraer la sal del final
  const saltBytes = hashWithSaltBytes.slice(hashSizeInBytes);

  // Recalcular el hash con el mismo salt
  const hash = crypto
    .createHash('sha512')
    .update(Buffer.concat([Buffer.from(plainText, 'utf8'), saltBytes]))
    .digest();

  // Volver a concatenar hash + salt
  const expectedHashBytes = Buffer.concat([hash, saltBytes]);

  // Convertir a Base64
  const expectedHashString = expectedHashBytes.toString('base64');

  // Comparar
  return storedHash === expectedHashString;
}

app.listen(PORT, () => {
  console.log(`La API está escuchando en el puerto ${PORT}`);
});
