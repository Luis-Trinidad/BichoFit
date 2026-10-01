import SessionController from './SessionController'
import SetController from './SetController'
import HistoryController from './HistoryController'

const Workout = {
    SessionController: Object.assign(SessionController, SessionController),
    SetController: Object.assign(SetController, SetController),
    HistoryController: Object.assign(HistoryController, HistoryController),
}

export default Workout