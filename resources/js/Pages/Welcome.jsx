import { Head } from '@inertiajs/react';
import { useEffect } from 'react';

export default function Welcome() {
    useEffect(() => {
        const newBuildingChannel = window.Echo.channel('new-building-created');
        const newBuildingListener = (e) => {
            console.log('NewBuildingCreated:', e);
        };
        newBuildingChannel.listen('.new.building.created', newBuildingListener);

        const newElevatorChannel = window.Echo.channel('new-elevator-created');
        const newElevatorListener = (e) => {
            console.log('NewElevatorCreated:', e);
        };
        newElevatorChannel.listen('.new.elevator.created', newElevatorListener);

        const elevatorActionsChannel = window.Echo.channel('elevator-actions');
        const elevatorActionListener = (e) => {
            console.log('ElevatorActionEvent:', e);
        };
        elevatorActionsChannel.listen('.elevator-action', elevatorActionListener);

        return () => {
            // Unsubscribe from channels when component unmounts
            newBuildingChannel.stopListening('.new.building.created', newBuildingListener);
            newElevatorChannel.stopListening('.new.elevator.created', newElevatorListener);
            elevatorActionsChannel.stopListening('.elevator-action', elevatorActionListener);
        };
    }, []); // Empty dependency array means this effect runs only on mount and unmount

    return (
        <>
            <Head title="Welcome" />
            <h1>Welcome</h1>
        </>
    );
}
