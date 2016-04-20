<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Canvas</title>
    <link href="style.css" rel="stylesheet"/>
</head>
<body onload="javascript:init()">
    <main>
        <div class="controls">
            <button class="button" onclick="javascript:saveCanvas();">Save</button>
        </div>
        <canvas id="canvas"></canvas>
    </main>
    <script src="https://code.createjs.com/createjs-2015.11.26.min.js"></script>
    <script id="editable">
    var canvas, image, stage,
        drawingCanvas, bitmap, reveal,
        cursor, radius = minRadius = maxRadius = 100, isUpdatingRadius,
        startPoint, endPoint, maskFilter, blurRadius = 100;

    function saveCanvas() {
        cursor.graphics.clear();
        stage.update();
    }

    function init() {
        canvas = document.getElementById('canvas');

        image = new Image();
        image.onload = handleComplete;
        image.src = "test.jpg";

        stage = new createjs.Stage("canvas");
    }

    function handleComplete() {
        stage.canvas.width = image.width;
        stage.canvas.height = image.height;

        maxRadius = image.height > image.width ? image.width / 4 : image.height / 4;
        maxRadius = maxRadius < minRadius ? minRadius : maxRadius;

        blurRadius = image.height > image.width ? image.width * 0.1 : image.height * 0.1;

        createjs.Touch.enable(stage);

        stage.enableMouseOver();

        stage.addEventListener("click", handleClick);
        stage.addEventListener("stagemousemove", handleMouseMove);
        stage.addEventListener("pressmove", handlePressMove);
        stage.addEventListener("pressup", handlePressUp);
        bitmap = new createjs.Bitmap(image);

        // Scale the image

        blur = new createjs.Bitmap(image);
        blur.filters = [
            new createjs.BlurFilter(blurRadius, blurRadius, 1),
            new createjs.ColorMatrixFilter(new createjs.ColorMatrix(20))
        ];
        blur.cache(0, 0, image.width, image.height);

        stage.addChild(blur, bitmap);

        reveal = new createjs.Shape();
        
        updateCacheImage(false);

        cursor = new createjs.Shape();
        redrawCursor();
        cursor.cursor = "pointer";

        stage.addChild(cursor);
    }

    function redrawCursor() {
        cursor.graphics.beginRadialGradientFill(
            ["rgba(255,0,0,0.5)", "rgba(255,0,0,0.5)", "rgba(255,0,0,0)"],
            [0, 0.9, 1], 0, 0, 0, 0, 0, radius).drawCircle(0, 0, radius);
    }

    function handleClick(event) {
        startX = stage.mouseX;
        startY = stage.mouseY;

        reveal.graphics.clear();
        reveal.graphics.beginFill("red").drawCircle(event.stageX,event.stageY,radius);
        updateCacheImage(true);
    }

    function calcDistance(x1, y1, x2, y2) {
        return Math.sqrt((x2 -= x1)*x2 + (y2-=y1)*y2);
    }

    function relativeDistance(x1, y1, x2, y2) {
        return (x1 < x2 ? 1 : -1) * calcDistance(x1, y1, x2, y2);
    }

    function handleMouseMove(event) {
        if (!isUpdatingRadius) {
            cursor.x = stage.mouseX;
            cursor.y = stage.mouseY;

            stage.update();
            return;
        }
    }

    function handlePressMove(event) {
        if (!isUpdatingRadius) {
            isUpdatingRadius = true;
            startX = event.stageX;
            startY = event.stageY;
            return;
        }

        radius = relativeDistance(startX, startY, stage.mouseX, stage.mouseY);
        radius = Math.abs(radius);
        radius = radius < minRadius ? minRadius : radius;
        radius = radius > maxRadius ? maxRadius : radius;

        cursor.graphics.clear();
        redrawCursor();

        // Increase radius
        stage.update();
    }

    function handlePressUp(event) {
        isUpdatingRadius = false;
    }

    function updateCacheImage(update) {
        if (update) {
            reveal.updateCache();
        } else {
            reveal.cache(0, 0, image.width, image.height);
        }

        maskFilter = new createjs.AlphaMaskFilter(reveal.cacheCanvas);

        bitmap.filters = [maskFilter];

        if (update) {
            bitmap.updateCache(0, 0, image.width, image.height);
        } else {
            bitmap.cache(0, 0, image.width, image.height);
        }

        stage.update();
    }
</script>
</body>
</html>