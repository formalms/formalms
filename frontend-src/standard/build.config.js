
const path = require('path');


module.exports = {
  entry: {
    main: './src/scripts/main.js'
  },
  output: {
    path: __dirname + '/../../html/templates/standard/static',
    publicPath: './static/',
    filename: '[name].js',
    chunkFilename: '[name].js'
  },
  cache: {
    type: 'filesystem',
    cacheLocation: path.resolve(__dirname, '/.build_cache'),
  },
  module: {
    rules: [
      {
        test: /\.scss$/,
        loaders: ['style-loader', 'css-loader', 'sass-loader']
      },
      {
        test: /\.css$/,
        loaders: ['style-loader', 'css-loader', 'sass-loader']
      },
      {
        test: /\.js$/,
        enforce: 'pre',
        loader: 'eslint-loader',
        exclude: [/(node_modules)/, __dirname + '/' + 'src/scripts/vendors'],
        options: {
          configFile: './.eslintrc'
        }
      },
      {
        test: /\.js$/,
        exclude: [/(node_modules)/],
        use: {
          loader: 'babel-loader',
          options: {
            presets: ['env','react', 'es2015', 'stage-0']
          }
        }
      },
      {
        test: /\.jsx$/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: ['env','react', 'es2015', 'stage-0']
          }
        }
      },
      {
        test: /\.twig$/,
        loader: 'twig-loader'
      },
      {
        test: /(jquery-mousewheel|malihu-custom-scrollbar-plugin)/,
        loader: 'imports-loader?define=>false&this=>window'
      }
    ]
  },
 // resolve: {
 //   alias: {
 //     Config: path.resolve(
 //       __dirname,
 //       'src/scripts/config/config.js'
 //     )
 //   }
 // },
  plugins: [
  ]
};


