const UglifyJSPlugin = require('uglifyjs-webpack-plugin');
const ConfigFile = require(__dirname + '/.ciffisettings');
const path = require('path');
const HardSourceWebpackPlugin = require('hard-source-webpack-plugin');

module.exports = {
  entry: {
    main: './' + ConfigFile.srcPathName + '/scripts/main.js'
  },
  output: {
    path: __dirname + '/' + ConfigFile.assetsPath,
    publicPath: ConfigFile.publicPath,
    filename: '[name].js',
    chunkFilename: '[name].js'
  },
  module: {
    rules: [
      {
        test: /\.scss$/,
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
        exclude: /(node_modules)/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: ['env', 'react', 'es2015', 'stage-0']
          }
        }
      },
      {
        test: /\.jsx$/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: ['env', 'react', 'es2015', 'stage-0']
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
  resolve: {
    alias: {
      Config: path.resolve(
        __dirname,
        ConfigFile.srcPathName + '/scripts/config/config.js'
      )
    }
  },
  plugins: [
    new HardSourceWebpackPlugin(),
    new UglifyJSPlugin({
      uglifyOptions: {
        sourceMap: false,
        comments: false
      }
    })
  ]
};
