import DashboardController from './DashboardController'
import Workout from './Workout'
import BodyScanController from './BodyScanController'
import VersionController from './VersionController'
import ProgressController from './ProgressController'
import ExerciseController from './ExerciseController'
import RoutineController from './RoutineController'
import RoutineItemController from './RoutineItemController'
import Settings from './Settings'

const Controllers = {
    DashboardController: Object.assign(DashboardController, DashboardController),
    Workout: Object.assign(Workout, Workout),
    BodyScanController: Object.assign(BodyScanController, BodyScanController),
    VersionController: Object.assign(VersionController, VersionController),
    ProgressController: Object.assign(ProgressController, ProgressController),
    ExerciseController: Object.assign(ExerciseController, ExerciseController),
    RoutineController: Object.assign(RoutineController, RoutineController),
    RoutineItemController: Object.assign(RoutineItemController, RoutineItemController),
    Settings: Object.assign(Settings, Settings),
}

export default Controllers