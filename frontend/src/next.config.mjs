/** @type {import('next').NextConfig} */
const nextConfig = {
  output: 'export',  // Genera sitio estático
  distDir: 'out',    // Directorio de salida
  images: {
    unoptimized: true, // Necesario para exportación estática
  },
  trailingSlash: true, // Añade / al final para archivos index.html
  basePath: '/Hotel_tame', // IMPORTANTE: Base path para subdirectorio
  assetPrefix: '/Hotel_tame/', // Para assets
}

export default nextConfig
