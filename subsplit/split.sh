git subsplit init git://github.com/koriym/BEAR.Sunday.git
git subsplit publish "
    src/BEAR/Sunday/Extension/Application:git@github.com:BEARSunday/application.git
    src/BEAR/Sunday/Extension/ApplicationLogger:git@github.com:BEARSunday/application-logger.git
    src/BEAR/Sunday/Extension/ConsoleOutput:git@github.com:BEARSunday/console-output.git
    src/BEAR/Sunday/Extension/ResourceView:git@github.com:BEARSunday/resource-view.git
    src/BEAR/Sunday/Extension/Router:git@github.com:BEARSunday/router.git
    src/BEAR/Sunday/Extension/TemplateEngine:git@github.com:BEARSunday/template-engine.git	
    src/BEAR/Sunday/Extension/WebResponse:git@github.com:BEARSunday/web-response.git
"--heads=master
