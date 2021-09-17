var Config = (function() {
  let newVar = {
    baseUrl: () => {
      const prefixes = [
        'appCore',
        'appScs',
        'appLms',
      ];
      let baseUrlArr = null;
      let res = window.location.origin;

      prefixes.forEach((prefix) => {
        baseUrlArr = window.location.href.split(`/${prefix}`, 2);
        if (baseUrlArr.length === 2) {
          res = baseUrlArr[0];
        }
      });
      return res;
    }
  };
  return newVar;
})();

module.exports = Config;