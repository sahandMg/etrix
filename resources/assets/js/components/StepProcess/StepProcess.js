import React, { Component } from 'react';
import axios from 'axios';
import { Link } from 'react-router-dom';
import URLs from '../../URLs';
import './StepProcess.css';
class StepProcess extends Component {
    state = {
    }

    componentDidMount() {
    }

    render() {

        return (
            <div className="step-container">
                <input type="checkbox" id="step-1" name="toggler" />
                <div className="step-dot one">Ready</div>
                <input type="checkbox" id="step-2" name="toggler" checked/>
                <div className="step-dot two">Set</div>
            </div>
            )
    }
}

export default StepProcess;