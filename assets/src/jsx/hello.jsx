import React from 'react'
import reactDom from 'react-dom';
class HelloMessage extends React.Component {
    render() {
      return (
        <div>
          Salut {this.props.name}
        </div>
      );
    }
  }
  
  reactDom.render(
    <HelloMessage name="Thierry" />,
    document.getElementById('hello-example')
  );