(function() {
  var PROJECT_ROOT, Settings, i, iniparser, mode, _len, _merge, _mode, _modes, _ref, _settings, _stop;

  iniparser = require('iniparser');

  PROJECT_ROOT = __dirname + '/../';

  _mode = (_ref = process.env.PROJECT_MODE) != null ? _ref : "live";

  _modes = ["live", "demo", "build", "test", "ci"];

  _settings = {};

  _stop = 0;

  for (i = 0, _len = _modes.length; i < _len; i++) {
    mode = _modes[i];
    if (mode === _mode) _stop = i;
  }

  _merge = function(from, to) {
    var final, key, value;
    final = {};
    for (key in from) {
      value = from[key];
      final[key] = from[key];
    }
    for (key in to) {
      value = to[key];
      final[key] = to[key];
    }
    return final;
  };

  Settings = {
    loadFromFile: function(file) {
      var group, newSettings, settings, _results;
      newSettings = iniparser.parseSync(file);
      _results = [];
      for (group in newSettings) {
        settings = newSettings[group];
        if (_settings[group] != null) {
          _results.push(_settings[group] = _merge(_settings[group], settings));
        } else {
          _results.push(_settings[group] = settings);
        }
      }
      return _results;
    },
    loadStandardSettings: function() {
      var i, mode, _len2, _results;
      _results = [];
      for (i = 0, _len2 = _modes.length; i < _len2; i++) {
        mode = _modes[i];
        if (i <= _stop) {
          _results.push(Settings.loadFromFile("" + PROJECT_ROOT + "settings/" + mode + ".ini"));
        }
      }
      return _results;
    },
    getValue: function(section, key, defaultValue) {
      var matches, value, _ref2, _ref3;
      if (defaultValue == null) defaultValue = null;
      value = (_ref2 = (_ref3 = _settings[section]) != null ? _ref3[key] : void 0) != null ? _ref2 : defaultValue;
      matches = value.match(/^\"(.+)\"$/);
      if (matches) return matches[1];
      return value;
    }
  };

  Settings.loadStandardSettings();

  module.exports = Settings;

}).call(this);
