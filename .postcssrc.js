module.exports = {
  plugins: [
    require('autoprefixer')({
      grid: "autoplace"
    }),
    require('postcss-logical')({ dir: 'ltr' })
  ]
}
