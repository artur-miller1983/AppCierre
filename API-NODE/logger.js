const fs = require('fs');
const path = require('path');
const { createLogger, format, transports } = require('winston');

// Definir carpeta absoluta basada en la ubicación de este archivo
const logDir = path.join(__dirname, 'logs');

// Crear la carpeta si no existe
if (!fs.existsSync(logDir)) {
  fs.mkdirSync(logDir);
}

const logger = createLogger({
  level: 'info',
  format: format.combine(
    format.timestamp({ format: 'YYYY-MM-DD HH:mm:ss' }),
    format.printf(({ timestamp, level, message }) => {
      return `[${timestamp}] ${level.toUpperCase()}: ${message}`;
    })
  ),
  transports: [
    new transports.Console(), // logs en consola (docker logs)
    new transports.File({ filename: path.join(logDir, 'error.log'), level: 'error' }), // solo errores
    new transports.File({ filename: path.join(logDir, 'combined.log') }) // todo
  ]
});

module.exports = logger;
