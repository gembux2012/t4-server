var Windows = function() {
    // Приватная функция
    var log = function(message) {
        console.log(message);
    };

    // Публичное свойство
    this.name = name;

    // Публичный метод
    this.logger = function(message) {
        log('Public ' + message);
    };
};

var w = new Windows();