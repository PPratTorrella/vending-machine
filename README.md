## Setup

1. Clone the project (master branch) and navigate to the root folder.
2. Open your terminal (or Command Prompt) and run the following command:
```bash
   docker-compose up -d --build
```

Once the setup completes, you can access the application at: 
http://localhost:8080/vending-machine

Commments:
In the final hours of working on this test, I focused on cleaning up the code by addressing all @todo comments. I imporved encapsulation, dependencies on interfaces, and properly injecting these around.

While reviewing the Command classes, I noticed they remained tightly coupled to the internals of the VendingMachine class, which is bad. My initial intention for using the Command pattern was tomake these async queued jobs. However, since I noticed I would probably not have time to implement this, I kept the commands working but tightly coupled as I though i might end up deleting them anyway.

Given more time, I would have reconsidered the necessity of the Command classes. I was hesitant to simply move all the command logic directly into the VendingMachine engine because it was already way too bloated. I also didn't want to artificially split the logic into several public methods just to accommodate the commands having anything to do. I knew i would probably need to extract away some repsonsability from the engine to new or existing services.

With a few more hours I would have refactored the VendingMachine engine to delegate some of its internal logic and object manipulation to separate service classes. For example, the Inventory object inside the VendingMachine could be a simple model for data purplooses, while the Inventory service could handle manipulating this model and updating the VendingMachine after.

This way I would have relocated logic away from the engine, and then decided either to remove Commands or at least decouple them from any thing they should not know about 
