const path = require('path');
const CopyWebpackPlugin = require("copy-webpack-plugin");

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
  devtool: 'source-map',
  optimization: {
    minimize: false
  },
  module: {
    rules: [
      {
        test: /\.scss$/,
        use: ['style-loader', 'css-loader', 'sass-loader']
      },
      {
        test: /\.less$/,
        use: ['style-loader', 'css-loader', 'less-loader']
      }, {
        test: /\.css$/,
        use: ['style-loader', 'css-loader']
      },
           /*
      {
        test: /\.js$/,
        enforce: 'pre',
        use: {
          loader: 'eslint-webpack-plugin',
          options: {
            configFile: './.eslintrc'
          }
        },
        exclude: [/node_modules/, __dirname + '/' + 'src/scripts/vendors'],
      },
      */
      {
        test: /\.js$/,
        exclude: [/node_modules/],
        use: {
          loader: 'babel-loader',
          options: {
            // presets: ['@babel/preset-env'],
            presets: ['@babel/preset-env','@babel/preset-react'],
            plugins: ['@babel/plugin-transform-runtime']
          }
        }
      },
      /*
      {
        test: /\.jsx$/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: ['env', 'react', 'es2015', 'stage-0']
          }
        }
      },
      */
      {
        test: /\.twig$/,
        use: 'twig-loader'
      },
      {
        test: /(jquery-mousewheel|malihu-custom-scrollbar-plugin)/,
        use: 'imports-loader?define=>false&this=>window'
      }
    ]
  },
  resolve: {
    alias: {
      Config: path.resolve(
        __dirname,
        'src/scripts/config/config.js'
      )
    }
  },
  plugins: [
    new CopyWebpackPlugin({
      patterns: [
        { from: 'src/images/', to: __dirname + '/../../html/templates/standard/static/images' },
        { from: 'src/fonts/', to: __dirname + '/../../html/templates/standard/static/fonts' }
      ]
    })
  ]
};
